<?php

// Encolar los assets (CSS y JS)
function webheroe_chatbot_ia_enqueue_assets() {
    wp_enqueue_style( 'webheroe-chatbot-ia-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
    wp_enqueue_script( 'webheroe-chatbot-ia-script', plugin_dir_url( __FILE__ ) . 'assets/js/chatbot.js', array(), '1.0', true );

    wp_localize_script( 'webheroe-chatbot-ia-script', 'webheroe_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'webheroe_chatbot_nonce' )
    ));
}
add_action( 'wp_enqueue_scripts', 'webheroe_chatbot_ia_enqueue_assets' );

add_action('wp_ajax_webheroe_chatbot_ia_process_input', 'webheroe_chatbot_ia_process_input');
add_action('wp_ajax_nopriv_webheroe_chatbot_ia_process_input', 'webheroe_chatbot_ia_process_input');


// Función para agregar un nuevo texto
function webheroe_chatbot_ia_add_text() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'chatbot_textos';

    // Verificar si se envió el formulario de nuevo texto
    if (isset($_POST['submit_new'])) {
        $texto_largo = sanitize_textarea_field($_POST['texto_largo']);
        $respuesta_corta = sanitize_text_field($_POST['respuesta_corta']);
        
        if (empty($texto_largo) || empty($respuesta_corta)) {
            echo '<div class="error"><p>Por favor, rellena todos los campos.</p></div>';
        } else {
            // Insertar el nuevo texto en la base de datos
            $wpdb->insert(
                $table_name,
                [
                    'texto_largo' => $texto_largo,
                    'respuesta_corta' => $respuesta_corta,
                ]
            );

            echo '<div class="updated"><p>Nuevo texto agregado correctamente.</p></div>';
        }
    }

    // Formulario para agregar un nuevo texto
    ?>
    <div class="wrap">
        <h1>Agregar Nuevo Texto de Referencia</h1>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Texto Largo</th>
                    <td><textarea name="texto_largo" rows="5" cols="50" required></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Respuesta Corta</th>
                    <td><input type="text" name="respuesta_corta" required /></td>
                </tr>
            </table>
            <input type="submit" name="submit_new" class="button-primary" value="Agregar Texto" />
        </form>
    </div>
    <?php
}

// Función para editar un texto existente
function webheroe_chatbot_ia_edit_text($id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'chatbot_textos';

    // Obtener los datos del texto actual
    $texto = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id");

    if (!$texto) {
        return false; // Si no se encuentra el texto, retornar false
    }

    // Verificar si se ha enviado el formulario de edición
    if (isset($_POST['submit_edit'])) {
        // Sanear y actualizar los datos
        $texto_largo = sanitize_textarea_field($_POST['texto_largo']);
        $respuesta_corta = sanitize_text_field($_POST['respuesta_corta']);

        if (empty($texto_largo) || empty($respuesta_corta)) {
            echo '<div class="error"><p>Por favor, rellena todos los campos.</p></div>';
        } else {
            // Actualizar en la base de datos
            $wpdb->update(
                $table_name,
                [
                    'texto_largo' => $texto_largo,
                    'respuesta_corta' => $respuesta_corta,
                ],
                ['id' => $id]  // Donde id es el valor único
            );

            // Usar un mensaje de éxito antes de la redirección para evitar problemas de encabezados
            echo '<div class="updated"><p>Texto actualizado correctamente.</p></div>';

            // Evitar la redirección inmediata
            add_action('admin_footer', function() {
                echo '<script type="text/javascript">
                    setTimeout(function() {
                        window.location.href = "' . admin_url('admin.php?page=webheroe-chatbot-ia') . '";
                    }, 1000); // Redirige después de 1 segundo
                </script>';
            });

            return; // Salir del proceso para evitar la redirección prematura
        }
    }

    // Mostrar el formulario para editar
    ?>
    <div class="wrap">
        <h1>Editar Texto de Referencia</h1>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Texto Largo</th>
                    <td><textarea name="texto_largo" rows="5" cols="50" required><?php echo esc_textarea($texto->texto_largo); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Respuesta Corta</th>
                    <td><input type="text" name="respuesta_corta" value="<?php echo esc_attr($texto->respuesta_corta); ?>" required /></td>
                </tr>
            </table>
            <input type="submit" name="submit_edit" class="button-primary" value="Actualizar Texto" />
        </form>
    </div>
    <?php
}

// Función para eliminar un texto
function webheroe_chatbot_ia_delete_text($id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'chatbot_textos';

    // Verificar si se quiere eliminar el texto
    if (isset($_POST['submit_delete'])) {
        // Eliminar el texto de la base de datos
        $wpdb->delete(
            $table_name,
            ['id' => $id]
        );

        echo '<div class="updated"><p>Texto eliminado correctamente.</p></div>';

        // Usar un script para redirigir a la página de administración después de 1 segundo
        add_action('admin_footer', function() {
            echo '<script type="text/javascript">
                setTimeout(function() {
                    window.location.href = "' . admin_url('admin.php?page=webheroe-chatbot-ia') . '";
                }, 1000); // Redirige después de 1 segundo
            </script>';
        });

        return; // Salir para evitar un conflicto con la redirección
    }

    // Mostrar un formulario de confirmación de eliminación
    ?>
    <div class="wrap">
        <h1>Eliminar Texto de Referencia</h1>
        <form method="post">
            <p>¿Estás seguro de que deseas eliminar este texto?</p>
            <input type="submit" name="submit_delete" class="button-primary" value="Eliminar Texto" />
        </form>
    </div>
    <?php
}

// Función para mostrar los textos guardados en la base de datos
function webheroe_chatbot_ia_show_textos() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_textos';
    $textos = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1>Gestión de Textos</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Texto Largo</th>
                    <th>Respuesta Corta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($textos as $texto) : ?>
                    <tr>
                        <td><?php echo $texto->id; ?></td>
                        <td><?php echo wp_trim_words($texto->texto_largo, 10, '...'); ?></td>
                        <td><?php echo $texto->respuesta_corta; ?></td>
                        <td>
                            <a href="?page=webheroe-chatbot-ia&action=edit&id=<?php echo $texto->id; ?>">Editar</a> | 
                            <a href="?page=webheroe-chatbot-ia&action=delete&id=<?php echo $texto->id; ?>" onclick="return confirm('¿Seguro que deseas eliminar este texto?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Función para mostrar la página de administración del plugin
function webheroe_chatbot_ia_admin_page() {
    // Verificar si se debe mostrar el formulario de edición o eliminación
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        webheroe_chatbot_ia_edit_text($_GET['id']);
    } elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        webheroe_chatbot_ia_delete_text($_GET['id']);
    } else {
        // Mostrar el formulario para agregar nuevo texto
        webheroe_chatbot_ia_add_text();
        // Mostrar los textos guardados
        webheroe_chatbot_ia_show_textos();
    }
}

// functions.php

// Agregar una página de configuración
function webheroe_chatbot_ia_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de WebHeroe Chatbot IA</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('webheroe_chatbot_ia_settings');
            do_settings_sections('webheroe_chatbot_ia_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Token de Hugging Face</th>
                    <td><input type="text" name="webheroe_chatbot_ia_hf_token" value="<?php echo esc_attr(get_option('webheroe_chatbot_ia_hf_token')); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registrar la configuración
function webheroe_chatbot_ia_register_settings() {
    register_setting('webheroe_chatbot_ia_settings', 'webheroe_chatbot_ia_hf_token');
}
add_action('admin_init', 'webheroe_chatbot_ia_register_settings');

// Agregar el enlace a la página de configuración en el menú del plugin
function webheroe_chatbot_ia_admin_menu() {
    add_menu_page(
        'WebHeroe Chatbot IA',           // Título de la página
        'Chatbot IA',                    // Título del menú
        'manage_options',                // Capacidad
        'webheroe-chatbot-ia',           // Slug
        'webheroe_chatbot_ia_admin_page',   // Función que se muestra cuando se hace clic en el menú
        'dashicons-format-chat',         // Icono del menú
        6                                // Posición en el menú
    );

    // Agregar submenú para configuración
    add_submenu_page(
        'webheroe-chatbot-ia',
        'Configuración',
        'Configuración',
        'manage_options',
        'webheroe-chatbot-ia-settings',
        'webheroe_chatbot_ia_settings_page'
    );
}
add_action( 'admin_menu', 'webheroe_chatbot_ia_admin_menu' );

// functions.php

// functions.php

function hacer_solicitud_api($api_url, $headers, $body, $max_retries = 3) {
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $attempt = 0;
    $response = false;
    $http_code = 0;
    
    while ($attempt < $max_retries && !$response) {
        $response = curl_exec($ch);
        if ($response === false) {
            // Error en cURL
            $attempt++;
            sleep(2); // Esperar antes de reintentar
            continue;
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code == 503) {
            // Servicio no disponible, intentar de nuevo
            $response = false;
            $attempt++;
            sleep(2);
        } else {
            break;
        }
    }
    
    curl_close($ch);
    return array('response' => $response, 'http_code' => $http_code);
}

// Procesar la entrada del usuario
function webheroe_chatbot_ia_process_input() {
    // Verificar el nonce para seguridad
    check_ajax_referer( 'webheroe_chatbot_nonce', 'nonce' );

    // Obtener el mensaje del usuario
    $user_message = sanitize_text_field( $_POST['message'] );

    if ( empty( $user_message ) ) {
        wp_send_json_error( 'Mensaje vacío.' );
    }

    // Cachear respuestas frecuentes
    $cache_key = 'chatbot_response_' . md5($user_message);
    $cached_response = get_transient($cache_key);
    if ($cached_response) {
        wp_send_json_success( array( 'respuesta' => $cached_response ) );
    }

    // Obtener los textos de referencia de la base de datos
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_textos';
    $textos = $wpdb->get_results( "SELECT * FROM $table_name" );

    if ( empty( $textos ) ) {
        wp_send_json_error( 'No hay textos de referencia disponibles.' );
    }

    // Limitar el contexto
    $textos = array_slice($textos, 0, 5); // Limitar a 5 textos
    $contexto = '';
    foreach ( $textos as $texto ) {
        if (strlen($contexto) + strlen($texto->texto_largo) > 2000) { // Limitar a 2000 caracteres
            break;
        }
        $contexto .= $texto->texto_largo . "\n";
    }

    // Obtener el token de Hugging Face desde las opciones
    $hf_token = get_option( 'webheroe_chatbot_ia_hf_token' );

    if ( empty( $hf_token ) ) {
        wp_send_json_error( 'Token de Hugging Face no configurado.' );
    }

    // Preparar la solicitud a la API de Hugging Face
    $api_url = 'https://api-inference.huggingface.co/models/facebook/blenderbot-400M-distill'; // Modelo recomendado
    $headers = array(
        'Authorization' => 'Bearer ' . $hf_token,
        'Content-Type'  => 'application/json',
    );

    // Crear el prompt
    $prompt = "Contexto sobre WebHeroe:\n" . $contexto . "\n\nPregunta: " . $user_message . "\nRespuesta:";

    $body = json_encode( array(
        'inputs' => $prompt,
        'parameters' => array(
            'max_new_tokens' => 150,
            'temperature' => 0.7,
        ),
    ) );

    // Realizar la solicitud con reintentos
    $resultado = hacer_solicitud_api($api_url, $headers, $body);

    if ( !$resultado['response'] ) {
        wp_send_json_error( 'Error en la comunicación con la API de Hugging Face después de múltiples intentos.' );
    }

    $response = $resultado['response'];
    $http_code = $resultado['http_code'];

    if ( $http_code !== 200 ) {
        wp_send_json_error( 'Error en la API de Hugging Face: Código ' . $http_code );
    }

    $response_data = json_decode( $response, true );

    if ( isset( $response_data[0]['generated_text'] ) ) {
        // Extraer la respuesta generada
        $respuesta = trim( str_replace( $prompt, '', $response_data[0]['generated_text'] ) );

        // Limitar la respuesta
        $respuesta = wp_trim_words( $respuesta, 50, '...' );

        // Cachear la respuesta
        set_transient( $cache_key, $respuesta, HOUR_IN_SECONDS );

        wp_send_json_success( array( 'respuesta' => $respuesta ) );
    } else {
        wp_send_json_error( 'No se recibió una respuesta válida de la API.' );
    }
}
add_action('wp_ajax_webheroe_chatbot_ia_process_input', 'webheroe_chatbot_ia_process_input');
add_action('wp_ajax_nopriv_webheroe_chatbot_ia_process_input', 'webheroe_chatbot_ia_process_input');




/*
function webheroe_chatbot_ia_ask_huggingface($pregunta, $contexto) {
    error_log('webheroe_chatbot_ia_ask_huggingface se ejecutó correctamente.');

    // Validar si el contexto es genérico
    if (stripos($contexto, 'Lo siento') !== false) {
        return "No encontré información específica en nuestra base de datos para responder tu pregunta.";
    }

    $api_url = 'https://api-inference.huggingface.co/models/deepset/roberta-base-squad2';

    $contexto = limpiar_contexto($contexto);

    // Registrar contexto limpio
    error_log('Contexto limpio: ' . $contexto);

    $data = [
        'question' => $pregunta, // La pregunta directa
        'context' => $contexto   // Contexto relacionado con la pregunta
    ];

    $retry = 0;
    $max_retries = 5;
    $wait_time = 10; // Segundos

    do {
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer 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'  // Sustituir con tu token de Hugging Face
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 segundos de espera
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (!$response) {
            error_log('Error al conectar con Hugging Face: ' . curl_error($ch));
            wp_send_json_error(['message' => 'Error al procesar la solicitud.']);
            exit;
        }

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        
        if ($http_status === 503) {
            error_log('Modelo cargándose, reintentando en ' . $wait_time . ' segundos...');
            sleep($wait_time); // Espera antes de volver a intentar
            $retry++;
        } else {
            break; // Salir del bucle si el estado no es 503
        }
    } while ($retry < $max_retries);


    if ($http_status !== 200) {
        error_log("HTTP Status Code: " . $http_status);
        error_log("Response: " . $response);
        wp_send_json_error(['message' => 'Error al procesar la solicitud (HTTP ' . $http_status . ').']);
        exit;
    }

    $decoded_response = json_decode($response, true);
    
    // Registrar cualquier error
    if (isset($decoded_response['error'])) {
        error_log('Error en la respuesta de Hugging Face: ' . $decoded_response['error']);
        return "Error en la respuesta de la API: " . $decoded_response['error'];
    }

    if (empty($decoded_response['answer']) || $decoded_response['score'] < 0.1) {
        error_log('Respuesta vacía o irrelevante: ' . print_r($decoded_response, true));
        return "No encontré información relevante en el contexto proporcionado.";
    }

    return $decoded_response['answer'];
}

function obtener_embedding($texto) {
    $texto = strtolower($texto); // Convertir a minúsculas
    $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto); // Eliminar caracteres especiales
    $tokens = preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY); // Tokenizar

    // Calcular una representación simple
    $frecuencia = array_count_values($tokens);
    $total_tokens = count($tokens);

    $embedding = [];
    foreach ($frecuencia as $palabra => $count) {
        $embedding[] = $count / $total_tokens;
    }

    return $embedding;
}


function procesar_pregunta($pregunta) {
    global $wpdb;

    // Obtener todos los textos almacenados en la base de datos
    $table_name = $wpdb->prefix . 'chatbot_textos';
    $textos = $wpdb->get_results("SELECT texto_largo FROM $table_name");

    // Seleccionar los fragmentos más relevantes
    $fragmentos_relevantes = seleccionar_contexto_relevante($pregunta, $textos);

    // Crear el contexto para Hugging Face (solo los fragmentos más relevantes)
    $contexto = implode(' ', array_map(function($fragmento) {
        return $fragmento['texto'];
    }, $fragmentos_relevantes));

    // Enviar la pregunta y el contexto a Hugging Face para obtener la respuesta
    return webheroe_chatbot_ia_ask_huggingface($pregunta, $contexto);
}

function calculate_cosine_similarity($vector1, $vector2) {
    // Asegurar que los vectores tengan al menos un elemento
    $vector1 = $vector1 ?: [0];
    $vector2 = $vector2 ?: [0];

    // Hacer los vectores del mismo tamaño rellenando con ceros
    $max_length = max(count($vector1), count($vector2));
    $vector1 = array_pad($vector1, $max_length, 0);
    $vector2 = array_pad($vector2, $max_length, 0);

    $dot_product = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;

    foreach ($vector1 as $i => $val) {
        $dot_product += $val * $vector2[$i];
        $magnitude1 += $val * $val;
        $magnitude2 += $vector2[$i] * $vector2[$i];
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    return $magnitude1 > 0 && $magnitude2 > 0 
        ? $dot_product / ($magnitude1 * $magnitude2) 
        : 0;
}

function seleccionar_contexto_relevante($pregunta, $textos) {
    $pregunta_embedding = obtener_embedding($pregunta);
    $contextos_relevantes = [];
    $umbral_similitud = 0.2; // Ajustar según necesidad
    $max_caracteres_contexto = 1000; // Límite para enviar a la API

    foreach ($textos as $texto) {
        $texto_embedding = obtener_embedding($texto->texto_largo);
        $similitud = calculate_cosine_similarity($pregunta_embedding, $texto_embedding);

        error_log("Similitud con texto: " . substr($texto->texto_largo, 0, 50) . "... -> {$similitud}");

        // Considerar solo textos con similitud superior al umbral
        if ($similitud >= $umbral_similitud) {
            $contextos_relevantes[] = [
                'texto' => $texto->texto_largo,
                'similitud' => $similitud,
                'longitud' => strlen($texto->texto_largo)
            ];
        }
    }

    // Ordenar los contextos por similitud (descendente)
    usort($contextos_relevantes, function($a, $b) {
        return $b['similitud'] <=> $a['similitud'];
    });

    // Concatenar textos más relevantes sin exceder el límite de caracteres
    $contexto_seleccionado = '';
    foreach ($contextos_relevantes as $contexto) {
        if (strlen($contexto_seleccionado) + $contexto['longitud'] <= $max_caracteres_contexto) {
            $contexto_seleccionado .= $contexto['texto'] . " ";
        } else {
            break; // Detener concatenación si excede el límite
        }
    }

    $contexto_seleccionado = trim($contexto_seleccionado);

    // Validar el contexto seleccionado
    if (strlen($contexto_seleccionado) < 50) {
        error_log("Contexto seleccionado insuficiente: {$contexto_seleccionado}");
        return "Lo siento, no encontré información relevante para responder a esta pregunta.";
    }

    error_log("Contexto seleccionado: {$contexto_seleccionado}");
    return $contexto_seleccionado;
}



// Función para procesar el input del usuario
function procesar_input_usuario($input) {
    global $wpdb;
    
    // Obtener todos los textos almacenados en la base de datos
    $table_name = $wpdb->prefix . 'chatbot_textos';
    $textos = $wpdb->get_results("SELECT texto_largo, respuesta_corta FROM $table_name");

    // Seleccionar los fragmentos más relevantes
    $fragmentos_relevantes = seleccionar_contexto_relevante($input, $textos);

    // Crear el contexto para Hugging Face (solo los fragmentos más relevantes)
    $contexto = implode(' ', array_map(function($fragmento) {
        return $fragmento['texto'];
    }, $fragmentos_relevantes));

    // Obtener titulares
    $titulares = array_map(function($texto) {
        return $texto->respuesta_corta;
    }, $textos);

    // Verificar relevancia
    $relevancia = es_contenido_relevante($input) ? 
        'Sí, tiene relación con WebHeroe.' : 
        'No, no tiene relación con WebHeroe.';

    // Enviar la pregunta y el contexto a Hugging Face
    $respuesta = webheroe_chatbot_ia_ask_huggingface($input, $contexto);

    return [
        'relevante' => $respuesta,
        'titulares' => $titulares,
        'relevancia' => $relevancia
    ];
}

function es_contenido_relevante($input) {
    $palabras_clave = ['web', 'proyecto', 'digital', 'marketing', 'webheroe']; 

    $input_lower = strtolower($input);
    foreach ($palabras_clave as $palabra) {
        if (strpos($input_lower, $palabra) !== false) {
            return true;
        }
    }

    return false;
}

// Función para obtener los titulares de todos los textos
function obtener_titulares() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_textos';

    // Obtener los primeros 5 caracteres de todos los textos largos para generar titulares
    $textos = $wpdb->get_results("SELECT respuesta_corta FROM $table_name");

    $titulares = [];
    foreach ($textos as $texto) {
        $titulares[] = $texto->respuesta_corta; // Utilizamos la respuesta corta como titular
    }

    return $titulares;
}

// Función para procesar la entrada del chatbot
function webheroe_chatbot_ia_process_input() {
    error_log('webheroe_chatbot_ia_process_input se ejecutó.');
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'webheroe_chatbot_nonce')) {
        error_log('Nonce inválido.');
        wp_send_json_error(['message' => 'Nonce inválido.']);
        exit;
    }

    // Validar mensaje del usuario
    if (!isset($_POST['message']) || empty($_POST['message'])) {
        error_log('Mensaje vacío o no definido.');
        wp_send_json_error(['message' => 'Mensaje vacío o no definido.']);
        exit;
    }

    $user_message = sanitize_text_field($_POST['message']);
    error_log('Mensaje recibido: ' . $user_message);

    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_textos';

    // Obtener todos los textos almacenados
    $texts = $wpdb->get_results("SELECT id, texto_largo, respuesta_corta FROM $table_name");

    if (!$texts) {
        error_log('No se encontraron textos en la base de datos.');
        wp_send_json_error(['message' => 'No se encontraron textos almacenados.']);
        exit;
    }

    // Seleccionar el contexto más relevante
    $contexto = seleccionar_contexto_relevante($user_message, $texts);

    // Validar el contexto seleccionado
    if (!validar_contexto($contexto)) {
        wp_send_json_success([
            'relevante' => 'Actualmente no tenemos información precisa para responder.',
            'titulares' => [],
            'relevancia' => 'Baja'
        ]);
        exit;
    }

    // Registrar el contexto final seleccionado
    error_log("Contexto final enviado: {$contexto}");

    // Llamar a Hugging Face con la pregunta y el contexto
    $response = webheroe_chatbot_ia_ask_huggingface($user_message, $contexto);

    if (!$response) {
        error_log('Error al procesar la solicitud a Hugging Face.');
        wp_send_json_error(['message' => 'Error al procesar la solicitud.']);
        exit;
    }

    // Registrar la respuesta recibida
    error_log("Respuesta de Hugging Face: {$response}");

    // Responder con el texto más relevante
    wp_send_json_success([
        'relevante' => $response,
        'titulares' => explode(".", $contexto),
        'relevancia' => 'Alta'
    ]);
}

function limpiar_contexto($contexto) {
    $contexto = strip_tags($contexto); // Eliminar etiquetas HTML
    $contexto = preg_replace('/[\r\n]+/', ' ', $contexto); // Reemplazar saltos de línea por espacios
    $contexto = preg_replace('/[^\w\s.,-]/u', '', $contexto); // Eliminar caracteres especiales
    $contexto = trim($contexto); // Eliminar espacios extra
    return substr($contexto, 0, 1000); // Limitar a 1000 caracteres
}

function validar_contexto($contexto) {
    if (strlen($contexto) < 50) { // Contexto demasiado corto
        error_log('Contexto no válido: demasiado corto.');
        return false;
    }
    return true;
}
*/





