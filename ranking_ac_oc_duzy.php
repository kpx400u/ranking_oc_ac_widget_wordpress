<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
 <?php
/*
Plugin Name: OC/AC RANKING DUŻY PAKIET1
Description: Widget wyświetlający ranking ubezpieczeń OC/AC z możliwością edycji i zarządzania.
Version: 1.0
Author: Twoje Imię
*/

// Rejestracja widgetu
class Insurance_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'insurance_widget',
            __('Ranking Ubezpieczeń OC/AC', 'insurance_widget_domain'),
            array('description' => __('Widget z rankingiem ubezpieczeń OC/AC', 'insurance_widget_domain'))
        );
    }

    public function widget($args, $instance) {
        echo do_shortcode('[ranking_ubezpieczen]');
    }

    public function form($instance) {
        echo '<p>Skonfiguruj ranking ubezpieczeń OC/AC w Ustawieniach > Ranking Ubezpieczeń OC/AC.</p>';
    }
}

// Rejestracja widgetu
function register_insurance_widget() {
    register_widget('Insurance_Widget');
}
add_action('widgets_init', 'register_insurance_widget');

// Menu administracyjne
function insurance_widget_menu() {
    add_menu_page(
        'Ranking Ubezpieczeń OC/AC',
        'Ranking Ubezpieczeń OC/AC',
        'manage_options',
        'insurance-widget-settings',
        'insurance_widget_settings_page',
        'dashicons-shield-alt',
        23 // Pozycja w menu
    );
}
add_action('admin_menu', 'insurance_widget_menu');

// Strona ustawień dla ubezpieczeń OC/AC
function insurance_widget_settings_page() {
    $insurances = get_option('insurance_widget_data', array());

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['insurances'])) {
            $insurances = json_decode(stripslashes($_POST['insurances']), true);
            update_option('insurance_widget_data', $insurances);
        }
    }
    ?>
    <div class="wrap">
        <h1>Ranking Ubezpieczeń OC/AC</h1>
        <table id="insurances-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Przesuń</th>
                    <th>Wyłącz</th>
                    <th>Nazwa</th>
                    <th>Logo (URL)</th>
                    <th>Cena od</th>
                    <th>Zakres ochrony</th>
                    <th>Ocena (1-5)</th>
                    <th>Link</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody id="sortable-insurances">
                <?php foreach ($insurances as $index => $insurance) : ?>
                    <tr>
                        <td class="drag-handle">☰</td>
                        <td><input type="checkbox" class="disabled" <?php echo !empty($insurance['disabled']) ? 'checked' : ''; ?>></td>
                        <td><input type="text" value="<?php echo esc_attr($insurance['name']); ?>" class="name"></td>
                        <td><input type="text" value="<?php echo esc_url($insurance['logo']); ?>" class="logo"></td>
                        <td><input type="text" value="<?php echo esc_attr($insurance['price']); ?>" class="price"></td>
                        <td><input type="text" value="<?php echo esc_attr($insurance['coverage']); ?>" class="coverage"></td>
                        <td><input type="number" min="1" max="5" value="<?php echo intval($insurance['rating']); ?>" class="rating"></td>
                        <td><input type="url" value="<?php echo esc_url($insurance['link']); ?>" class="link"></td>
                        <td><button class="remove">Usuń</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button id="add-insurance">Dodaj ubezpieczenie</button>
        <button id="save-insurances" class="button-primary">Zapisz zmiany</button>

        <form id="insurances-form" method="POST" style="display: none;">
            <input type="hidden" name="insurances" id="insurances-data">
        </form>
    </div>

    <script>
       document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#sortable-insurances');
    const addBtn = document.querySelector('#add-insurance');
    const saveBtn = document.querySelector('#save-insurances');
    const form = document.querySelector('#insurances-form');
    const dataField = document.querySelector('#insurances-data');

    // Obsługa przeciągania i upuszczania za pomocą SortableJS
    new Sortable(table, {
        animation: 150,
        handle: '.drag-handle', // Obsługiwany uchwyt przeciągania
        onEnd: () => {
            console.log('Zmieniono kolejność ubezpieczeń!');
        }
    });

    // Dodanie nowego wiersza
    addBtn.addEventListener('click', () => {
        const row = table.insertRow();
        row.innerHTML = `
            <td class="drag-handle">☰</td>
            <td><input type="checkbox" class="disabled"></td>
            <td><input type="text" class="name"></td>
            <td><input type="text" class="logo"></td>
            <td><input type="text" class="price"></td>
            <td><input type="text" class="coverage"></td>
            <td><input type="number" min="1" max="5" class="rating"></td>
            <td><input type="url" class="link"></td>
            <td><button class="remove">Usuń</button></td>
        `;
    });

    // Usuwanie wiersza
    table.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove')) {
            e.target.closest('tr').remove();
        }
    });

    // Zapis danych
    saveBtn.addEventListener('click', () => {
        const rows = table.querySelectorAll('tr');
        const data = Array.from(rows).map(row => ({
            disabled: row.querySelector('.disabled').checked ? 1 : 0,
            name: row.querySelector('.name').value,
            logo: row.querySelector('.logo').value,
            price: row.querySelector('.price').value,
            coverage: row.querySelector('.coverage').value,
            rating: row.querySelector('.rating').value,
            link: row.querySelector('.link').value,
        }));
        dataField.value = JSON.stringify(data);
        form.submit();
    });
});

    </script>
    <?php
}
function insurance_shortcode() {
    $insurances = get_option('insurance_widget_data', array());
    $current_month = date_i18n('F');
    $current_year = date('Y');

    ob_start(); ?>
    <div class="loan-widget-shortcode">
        <h2>Ranking Ubezpieczeń OC/AC – <?php echo esc_html($current_month) . ' ' . esc_html($current_year); ?></h2>
        <div class="loan-list">
            <?php foreach ($insurances as $insurance) : ?>
                <?php if (empty($insurance['disabled'])) : ?>
                    <div class="loan-item">
                        <div><img src="<?php echo esc_url($insurance['logo']); ?>" alt="Logo"></div>
                        <div><p><strong>Cena od:</strong> <?php echo esc_html($insurance['price']); ?> zł</p></div>
                        <div><p><strong>Zakres ochrony:</strong> <?php echo esc_html($insurance['coverage']); ?></p></div>
                        <div><p><strong>Ocena:</strong> <?php echo str_repeat('⭐', intval($insurance['rating'])); ?></p></div>
                        <div><a href="<?php echo esc_url($insurance['link']); ?>" class="button">Sprawdź</a></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('ranking_ubezpieczen_duży', 'insurance_shortcode');






// Rejestracja i ładowanie plików CSS
function insurance_widget_enqueue_styles() {
    wp_enqueue_style(
        'insurance-widget-style', // Unikalny identyfikator
        plugins_url('css/loan-widget-style.css', __FILE__), // Ścieżka do pliku CSS
        array(), // Brak zależności
        '1.0', // Wersja pliku
        'all' // Typ urządzenia
    );
}
add_action('wp_enqueue_scripts', 'insurance_widget_enqueue_styles');


