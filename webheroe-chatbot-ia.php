<?php
/*
Plugin Name: WebHeroe Chatbot IA
Description: Un chatbot con IA para WordPress, gestionando textos de referencia y respondiendo preguntas con IA gratuita.
Version: 1.0
Author: Jose Manuel
Text Domain: webheroe-chatbot-ia
Domain Path: /languages
*/

 // Evitar acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WEBHEROE_CHATBOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WEBHEROE_CHATBOT_URL', plugin_dir_url( __FILE__ ) );

require_once WEBHEROE_CHATBOT_PATH . 'functions.php';


function webheroe_chatbot_ia_create_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'chatbot_textos'; // Prefijo para evitar conflictos con otras tablas
    $charset_collate = $wpdb->get_charset_collate();

    // SQL para crear la tabla
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        texto_largo text NOT NULL,
        respuesta_corta text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Requerir el archivo de actualización de base de datos de WordPress
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'webheroe_chatbot_ia_create_table' );

function webheroe_chatbot_ia_shortcode(){
    ob_start();
    ?>
    <!-- Botón flotante para abrir el chatbot -->
    <div id="chatbot-toggle-btn" class="chatbot-toggle-btn">
    <img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/Button_inicio.png'; ?>" alt="Chatbot" />
    </div>

    <!-- Ventana del chatbot -->
    <div id="chat-window" class="chat-window">
        <div id="chat-log" class="chat-log">
            <!-- Mensajes se agregarán aquí -->
        </div>
        <input type="text" id="chat-input" class="chat-input" placeholder="Escribe un mensaje..." />
        <button id="chat-submit" class="chat-submit">Enviar</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'webheroe_chatbot_ia', 'webheroe_chatbot_ia_shortcode' );


// Función de desactivación
function webheroe_chatbot_ia_deactivate() {
    // Aquí se podría agregar código para limpiar, como eliminar datos de la base de datos si es necesario.
}
register_deactivation_hook( __FILE__, 'webheroe_chatbot_ia_deactivate' );
