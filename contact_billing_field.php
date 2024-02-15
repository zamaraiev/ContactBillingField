<?php 
/*
 * Plugin Name: Contact Billing Field
 * Description: Addon for adding contact method form on shipping page
 * Version: 1.1
 * Author: Zamaraiev Dmytro
 * WC requires at least: 5.7
 * WC tested up to: 8.1
 * Requires at least: 5.5
 * Tested up to: 6.3
 */

// Завантаження текстового домену плагіна
function custom_plugin_load_textdomain() {
    load_plugin_textdomain('ContactBillingField', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'custom_plugin_load_textdomain');

// Додавання поля до розділу "billing address" на сторінці оплати
add_filter('woocommerce_after_checkout_billing_form', 'custom_checkout_fields');

function custom_checkout_fields() {
    echo '<div class="custom-checkout-fields">';

    woocommerce_form_field('no_phone_calls', array(
        'type' => 'checkbox',
        'class' => array('input-checkbox'),
        'label' => __('No quiero que me llamen', 'ContactBillingField'),
    ), WC()->checkout->get_value('no_phone_calls'));

    woocommerce_form_field('preferred_contact_method', array(
        'type' => 'select',
        'class' => array('select'),
        'label' => __('Cómodo método de comunicación', 'ContactBillingField'),
        'options' => array(
            '' => __('Seleccione', 'ContactBillingField'),
            'Whatsapp' => __('Whatsapp', 'ContactBillingField'),
            'Viber' => __('Viber', 'ContactBillingField'),
            'Telegram' => __('Telegram', 'ContactBillingField'),
        ),
    ), WC()->checkout->get_value('preferred_contact_method'));

    woocommerce_form_field('contact_phone', array(
        'type' => 'text',
        'class' => array('input-text'),
        'label' => __('Número de teléfono', 'ContactBillingField'),
    ), WC()->checkout->get_value('contact_phone'));

    echo '</div>';

    // JavaScript для керування відображенням полів
    echo '<script>
    jQuery(document).ready(function($) {
        var $noPhoneCallsCheckbox = $("input[name=\'no_phone_calls\']");
        var $preferredContactMethod = $("select[name=\'preferred_contact_method\']");
        var $phoneInput = $("input[name=\'contact_phone\']");
        var $preferredMethodLabel = $("label[for=\'preferred_contact_method\']");
        var $phoneLabel = $("label[for=\'contact_phone\']");
        
        function toggleFields() {
            if ($noPhoneCallsCheckbox.is(":checked")) {
                $preferredContactMethod.show();
                $phoneInput.show();
                $preferredMethodLabel.show();
                $phoneLabel.show();
                 $("#contact-user-message").show();
            } else {
                $preferredContactMethod.hide();
                $phoneInput.hide();
                $preferredMethodLabel.hide();
                $phoneLabel.hide();
                 $("#contact-user-message").hide();
            }
        }
        
        toggleFields();
        
        $noPhoneCallsCheckbox.change(function() {
            toggleFields();
        });
    });
    </script>';  
}


/*function add_custom_billing_fields($fields)
{
    $fields['billing']['no_phone_calls'] = array(
        'type' => 'checkbox',
        'label' => __('No quiero que me llamen', 'ContactBillingField'),
        'class' => array('form-row-wide'),
    );

    $fields['billing']['preferred_contact_method'] = array(
        'type' => 'select',
        'label' => __('Cómodo método de comunicación', 'ContactBillingField'),
        'class' => array('form-row-wide'),
        'options' => array(
            '' => __('Seleccione', 'ContactBillingField'),
            'Whatsapp' => __('Whatsapp', 'ContactBillingField'),
            'Viber' => __('Viber', 'ContactBillingField'),
            'Telegram' => __('Telegram', 'ContactBillingField')
        ),
    );

    $fields['billing']['contact_phone'] = array(
        'type' => 'text',
        'label' => __('Número de teléfono', 'ContactBillingField'),
        'class' => array('form-row-wide'),
    );

    

    return $fields;
} */

add_action('woocommerce_after_checkout_billing_form', 'display_custom_message_after_billing_fields');

function display_custom_message_after_billing_fields() {
    echo '<p id="contact-user-message">' . __('Text for massage', 'ContactBillingField') . '</p>';
}

// Збереження значення полів під час оформлення замовлення
add_action('woocommerce_checkout_update_order_meta', 'save_custom_checkout_fields');

// Збереження значення полів під час оформлення замовлення
add_action('woocommerce_checkout_update_order_meta', 'save_custom_checkout_fields');

function save_custom_checkout_fields($order_id) {
    if (isset($_POST['no_phone_calls'])) {
        update_post_meta($order_id, 'no_phone_calls', '1');
    } else {
        update_post_meta($order_id, 'no_phone_calls', '0');
    }

    if ($_POST['preferred_contact_method']) {
        $preferred_contact_method = sanitize_text_field($_POST['preferred_contact_method']);
        update_post_meta($order_id, 'preferred_contact_method', $preferred_contact_method);

        // Додавання до коментарів замовлення
        
    }

    if ($_POST['contact_phone']) {
        $contact_phone = sanitize_text_field($_POST['contact_phone']);
        update_post_meta($order_id, 'contact_phone', $contact_phone);

        // Додавання до коментарів замовлення
        $order = wc_get_order($order_id);
        $order->add_order_note("Contact Phone Number: $contact_phone");
    }
    
    $order = wc_get_order($order_id);
    $order->add_order_note('Preferred Contact Method:' . sanitize_text_field($_POST['preferred_contact_method']));
}

add_action('woocommerce_checkout_create_order', 'modify_order_notes_on_checkout', 10, 2);

function modify_order_notes_on_checkout($order, $data) {
    $method = sanitize_text_field($_POST['preferred_contact_method']);
    $contact_phone = sanitize_text_field($_POST['contact_phone']);

    $note = $order->get_customer_note();
    $order->set_customer_note( $method . ' ' . $contact_phone . '  /  ' . $note);
}




add_action( 'woocommerce_admin_order_data_after_billing_address', 'true_print_field_value', 25 );
 
function true_print_field_value( $order ) {

    $method = get_post_meta( $order->get_id(), 'preferred_contact_method', true );
    $contact_phone = get_post_meta( $order->get_id(), 'contact_phone', true );
	echo '<p><strong>Preferred method of communication:</strong><br>' . esc_html( $method ) . '</p>';
    echo '<p>' . esc_html($contact_phone) . '</p>';
    //$note = $order->get_customer_note();
    //$order->set_customer_note(  $note . ' 123' .$method . ' ' .$contact_phone);
}

// Додавання тексту для перекладу у файл перекладу
function add_translation_strings() {
    $strings = array(
        'No quiero que me llamen' => __('No quiero que me llamen', 'ContactBillingField'),
        'Cómodo método de comunicación' => __('Cómodo método de comunicación', 'ContactBillingField'),
        'Seleccione' => __('Seleccione', 'ContactBillingField'),
        'Whatsapp' => __('Whatsapp', 'ContactBillingField'),
        'Viber' => __('Viber', 'ContactBillingField'),
        'Telegram' => __('Telegram', 'ContactBillingField'),
        'Número de teléfono' => __('Número de teléfono', 'ContactBillingField'),
        'Text for massage' => __('Text for massage', 'ContactBillingField')
    );

    foreach ($strings as $key => $value) {
        __('', 'ContactBillingField', $value);
    }
}
add_action('init', 'add_translation_strings');


?>