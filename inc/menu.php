<?php 

function skpp_menu() {
	add_menu_page( 'Widget Programu Partnerskiego Strefa Kursów', 'SKPP Widget', 'manage_options', 'skpp_options', 'skpp_options', $icon_url = plugins_url( 'skpp_widget/images/skpp_icon.png' ) );
	add_submenu_page( 'skpp_options', 'Widget Programu Partnerskiego Strefa Kursów - Wybór produktów', 'Wybór produktów', 'manage_options', 'skpp_product_submenu', 'skpp_product_submenu' );
	add_submenu_page( 'skpp_options', 'Widget Programu Partnerskiego Strefa Kursów - Pomoc', 'Pomoc', 'manage_options', 'skpp_help_submenu', 'skpp_help_submenu' );
}

add_action( 'admin_menu' , 'skpp_menu' );

/**
 * Funkcja strony opcji
 */
function skpp_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __('You do not have sufficient permissions to access this page') );
	}

	if ( isset( $_REQUEST['action'] ) ) {
		if ( 'save' == $_REQUEST['action'] ) {		
			if ( $_REQUEST['skpp_product_style'] ) {
				update_option( 'skpp_product_style' , $_REQUEST['skpp_product_style'] );
			}
			update_option( 'skpp_text_color' , $_REQUEST['skpp_text_color'] );
			update_option( 'skpp_bg_color' , $_REQUEST['skpp_bg_color'] );
			update_option( 'skpp_speed' , $_REQUEST['skpp_speed'] );
			if ( is_numeric( $_REQUEST['skpp_partner_id'] ) ) {
				update_option( 'skpp_partner_id' , $_REQUEST['skpp_partner_id'] );
				?>
				<div class="notice updated">
					<p>Wszystkie zmiany zostały zapisane</p>
				</div>	
				<?php
			} else {
				?>
				<div class="notice error">
					<p>Sprawdź czy na pewno podałeś właściwy ID Partnera (tylko cyfry)</p>
				</div>
				<?php	
			}
		}
		
	}
	?>
	
	<div class="skpp-options-wrapper">

		<?php
			$feed_status = skpp_get_feed();
			if ( is_wp_error( $feed_status ) ) {
				if ( $feed_status->get_error_messages( 'curl' ) ) {
					echo '<span class="skpp-error">' . $feed_status->get_error_messages( 'curl' ) . '</span>';
				} elseif ( $feed_status->get_error_messages ( 'xml' ) ) {
					echo '<span class="skpp-error">' . $feed_status->get_error_messages( 'xml' ) . '</span>';
				}
			}
		 ?>
		
		<form method="post" class="wrap skpp-options">
			<h2>Ustawienia wtyczki Programu Partnerskiego SK</h2>

			<div class="skpp-options-header postbox">	
				<p>Tutaj możesz zmienić ustawienia wtyczki. Podaj swój identyfikator partnera, określ jakie produkty chcesz promować i zacznij zarabiać razem z nami! Więcej informacji na temat Programu Partnerskiego Strefy Kursów <a href ="http://strefakursow.pl/program_partnerski.html">znajdziesz tutaj</a>.</p>

				<h3>Twój identyfikator partnera</h3>
				<input type="text" name="skpp_partner_id" value="<?php echo get_option("skpp_partner_id"); ?>" />
				<p>Swój identyfikator partnera możesz znaleźć po zalogowaniu do <a href="https://strefakursow.pl/customer/login.html">konta klienta w serwisie Strefa Kursów.</a></p>
			</div>

			<div class="skpp-style-settings postbox">
				<h3>Ustawienia stylów</h3>
				<p>Tutaj możesz określić jak będą wyglądały boxy z promowanymi produktami. Wybierz jeden z dwóch stylów lub zmień kolory. Wartość koloru powinna być podana w zapisie hexadecymalnym np. #ff0000.</p>
				<!--  table z opcjami stylow -->
				<table class="skpp-style-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="skpp_product_style">Styl produktów</label>
							</th>
							<td>
								<fieldset>
									<input type="radio" value="skpp_dvd" name="skpp_product_style" <?php if ('skpp_dvd' == get_option( 'skpp_product_style' )) { echo 'checked="checked"';} ?>/>
									<label for="skpp_dvd">DVD (płyta)</label>
									<input type="radio" value="skpp_box" name="skpp_product_style" <?php if ('skpp_box' == get_option( 'skpp_product_style' )) { echo 'checked="checked"';} ?>/>
									<label for="skpp_box">BOX</label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="skpp_text_color">Kolor tekstu</label>
							</th>
							<td>
								<input type="text" name="skpp_text_color" value="<?php echo get_option('skpp_text_color'); ?>">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="skpp_bg_color">Kolor tła</label>
							</th>
							<td>
								<input type="text" name="skpp_bg_color" value="<?php echo get_option('skpp_bg_color'); ?>">
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="skpp-style-settings postbox">
				<h3>Ustawienia rotatora</h3>
				<!--  table z opcjami stylow -->
				<table class="skpp-style-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="skpp_speed">Czas wyświetlania slajdu</label>
							</th>
							<td>
								<input type="text" name="skpp_speed" value="<?php echo get_option('skpp_speed'); ?>">
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		
			<input type="hidden" name="action" value="save" />
			<input type="submit" class="button button-primary" value="Zapisz zmiany" />
		</form>

	</div>

<?php
}

/**
* Funkcja strony wyboru produktów
*/
function skpp_product_submenu() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __('You do not have sufficient permissions to access this page') );
	}

	?>

	<div class="skpp-options-wrapper">
		
		<h2>Tutaj możesz wybrać produkty, które chcesz promować.</h2>
		<!-- Formularz kategorii -->
		<form method="post">

			<div class="skpp-select-categories postbox">
				<select name="select_category" id="">
					<option value="">Pokaż wszystko</option>
					<?php
						$categories = skpp_get_all_categories();
						foreach ( $categories as $cat ) {
							echo '<option value="' . $cat . '" name="' . $cat . '">' . $cat . '</option>';
						}
					 ?>
				</select>
				<input type="hidden" name="action" value="update" />
				<input type="submit" class="button button-primary" name="category_select" value="Wybierz" />
			</div>

		</form>

		<!-- Lista produktow -->
		<form method="post">

			<?php 
				if ( isset($_REQUEST['action']) ) {
					if ( isset($_REQUEST['select-category']) ) {
						echo '<h3 class="current-cat">' . $_REQUEST['select_category'] . '</h3>';
					}
				} else {
					echo '<h3 class="current-cat">Wszystkie produkty</h3>';
				}
			 ?>

			<div class="skpp-product-list postbox">
				<table>
					<thead>
						<tr>
							<th></th>
							<th>ID</th>
							<th>Nazwa</th>
							<th>Cena</th>
						</tr>
					</thead>
					<tbody>
						<?php

						$items = skpp_get_all_products();

						$selected_products = array();

						if ( isset($_REQUEST['action']) && 'update' == $_REQUEST['action'] ) {
							$selected_category = array();
							$selected_category = $_REQUEST['select_category'];
							$items = skpp_get_products_by_category( $selected_category );
						} else {
							$items = skpp_get_all_products();
						}

						if ( isset($_REQUEST['action']) && 'save' == $_REQUEST['action'] ) {
							if ( isset($_REQUEST['product']) ) {
								$selected_products = $_REQUEST['product'];
							}
							update_option( 'user_products', $selected_products );
						} else {
							$selected_products = get_option( 'user_products' );
						}

						/* Pusta tablic jesli nie ma wybranych produktow */
						if ( ! $selected_products ) {
							$selected_products = array();
						}

						foreach ( $items as $item ) {
							?>
							
							<tr>
								<td>
									<input type="checkbox" name="product[]" <?php echo 'id="' . $item->id . '"' ?> <?php echo 'value="' . $item->id . '"'?> <?php if($item->id == in_array($item->id, $selected_products )) echo 'checked="checked"'; ?>/>
								</td>
								<td>
									<?php echo $item->id; ?>
								</td>
								<td>
									<?php echo htmlspecialchars_decode($item->title); ?>
								</td>
								<td>
									<?php echo $item->price; ?>
								</td>
							</tr>

							<?php
						}

						?>
					</tbody>
				</table>
			</div>

			<input type="hidden" name="action" value="save" />
			<input type="submit" class="button button-primary" value="Zapisz zmiany" />
		</form>

	</div>

	<?php

}

function skpp_help_submenu() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __('You do not have sufficient permissions to access this page') );
	}

	?>
	<div class="skpp-options">
		<div class="skpp-options-wrapper wrap">
			<h2>Pomoc</h2>
			<div class="postbox">
<p>Tutaj znajdziesz instrukcję używania wtyczki Programu Partnerskiego Strefa Kursów. Jeśli tylko masz jakieś pytania albo wątpliwości zachęcamy do kontaktu z nami.</p>
<p>Więcej informacji na temat programu Partnerskiego znajdziesz na stronie <a href="http://strefakursow.pl/program_partnerski.html">Program Parnterski Strefa Kursów</a></p>
<p>Kontakt: <a href="mailto:pp@strefakursow.pl">pp@strefakursow.pl</a></p>

<h3>Co zawiera wtyczka</h3>
<p>Wtyczka umożliwia wyświetlanie dowolnych produktów z oferty wydawnictwa Strefa Kursów. Kursy możesz wyświetlać na swojej stronie za pomocą czterech różnych narzędzi:</p>
<ul>
	<li><strong>Widget „Polecany kurs”</strong> – wyświetla pojedynczy produkt.</li>
	<li><strong>Widget „Rotator produktów”</strong> – wyświetla 5 losowych produktów.</li>
	<li><strong>Shortcode [product]</strong> – wyświetla pojedynczy produkt w treści wpisu/strony.</li>
	<li><strong>Shortcode [catalog]</strong> – wyświetla katalog produktów z określonej kategorii.</li>
</ul>

<h3>Link partnerski</h3>
<p>Najważniejszym elementem wtyczki jest link partnerski, który umożliwia naliczanie prowizji. Link generowany jest automatycznie pod warunkiem, że w opcjach wtyczki podasz swój Identyfikator Partnera. Aby to zrobić przejdź do głównej strony opcji (SKPP Widget w menu po lewej), wpisz swój Identyfikator w odpowiednim polu i kliknij na przycisku Zapisz zmiany.</p>

<h3>Najważniejsze opcje wyglądu</h3>
<p>Wtyczka oferuje kilka opcji, które pozwolą Ci lepiej dostosować jej wygląd do Twojej witryny. Najważniejszą z nich jest możliwość wyboru stylu produktów:</p>
<p><strong>Box</strong> – styl taki jak w serwisie strefakursow.pl</p>
<p><strong>DVD</strong> – uproszczony styl z obrazkiem okładki oraz płyty DVD.</p>
<p>Oprócz tego masz możliwość zdefiniowania własnego koloru tła oraz kol tekstu dla produktów. Pamiętaj o tym aby kolory podawać w formacie HTML np. #FF0000. Opcje wyglądu działają globalnie – oznacza to, że będą miały wpływ na wszystkie widgety oraz shortcode SKPP używane na twojej stronie.</p>

<h3>Jak używać widgetu „Polecany kurs”</h3>
<p>Widget wyświetla jeden produkt. Domyślnie jest on losowy wybierany z pełnej oferty szkoleń. Możesz przejść do ustawień widgetu i wprowadzić ID konkretnego produktu, który chcesz za pomocą widgetu promować. Możesz także wybrać zestaw produktów w opcjach pluginu (SKPP Widget -> Wybór produktów). Widget będzie wtedy losowo wybierał jeden produkt z zaznaczonego przez ciebie zestawu.</p>

<h3>Jak używać widgetu „Rotator produktów”</h3>
<p>Widget wyświetla w formie prostej karuzeli 5 produktów. Domyślnie są one wybierane z pełnej listy szkoleń. Możesz w opcjach wtyczki wskazać dowolny zestaw produktów, które będą wyświetlane za pomocą rotatora. Na głównej stronie opcji wtyczki znajdziesz także pole do którego możesz wpisać czas wyświetlania pojedynczego slajdu. Czas podajemy w milisekundach – jeśli chcesz ustawić np. na 3 sekundy to wpisujesz do pola 3000. </p>

<h3>Jak korzystać z shortcode [product]</h3>
<p>Shortcode umożliwia wyświetlenie pojedynczego produktu w treści strony/wpisu. Kod jaki należy wprowadzić do treści wygląda następująco: <em>[skpp_product id='1046']</em>. Parametr ‘id’ to identyfikator produktu, który chcesz wyświetlić. Identyfikatory znajdziesz na stronie Wybór produktów w pierwszej kolumnie obok nazwy każdego kursu.</p>

<h3>Jak korzystać z shortcode [catalog]</h3>
<p>Ten shortcode umożliwia wyświetlenie zestawu kursów z określonej kategorii.  Kod jaki należy wprowadzić do treści wygląda następująco: <em>[skpp_catalog category='Web design' number='6' price='true' title='true']</em>. Poszczególne parametry:</p>
<ul>
	<li><strong>category</strong> – Nazwa kategorii kursów. Listę wszystkich kategorii znajdziesz na rozwijanej liście na stronie Wybór produktów. </li>
	<li><strong>number</strong> – Ilość produktów z danej kategorii.</li>
	<li><strong>price</strong> – Czy wyświetlać cenę (wartości true/false).</li>
	<li><strong>title</strong> - Czy wyświetlać tytuł (wartości true/false).</li>
</ul>
<p>Dwie ostatnie opcje dotyczą tylko produktów w stylu DVD!</p>

<h3>Jak wyświetlić produkty, które są aktualnie na promocji</h3>
<p>Obydwa widgety umożliwiają wyświetlenie produktów, które są aktualnie na promocji w serwisie strefakursow.pl. Wystarczy w opcjach danego widgetu zaznaczyć pozycję „Tylko produkty na promocji”.</p>

<h3>Aktualizacja listy kursów</h3>
<p>Ponieważ oferta Strefy Kursów wciąż rozszerza się o nowe pozycji to do wtyczki wprowadziliśmy prosty mechanizm aktualizujący. Plik XML z listą wszystkich kursów jest odświeżany przez wtyczkę raz na tydzień.</p>

<h3>Typowe problemy</h3>
<p>Wtyczka po aktywacji próbuje nawiązać połączenie z serwerem Strefa Kursów aby pobrać z niego plik XML z aktualną listą kursów. Jeśli na tym etapie pojawią się pewne problemy to zazwyczaj na głównej stronie opcji pojawi się jeden z dwóch komunikatów:</p>
<p><em>Brak cURL na serwerze!</em> – Oznacza on, że na twoim serwerze brakuje biblioteki cURL. W takiej sytuacji skontaktuj się z dostawcą/administratorem serwera.</p>
<p><em>Błąd XML</em> – Oznacza on, że z jakiegoś powodu wtyczka nie mogła pobrać albo zapisać pliku XML z listą kursów. Taki błąd może być często spowodowanym chwilowymi problemami z połączeniem. W takiej sytuacji najlepiej po prostu trochę poczekać.</p>
			</div>
		</div>
	</div>
<?php
}
?>