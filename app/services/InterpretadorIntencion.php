<?php
/**
 * Servicio: InterpretadorIntencion
 * 
 * Interpreta el mensaje del usuario y devuelve la intención.
 * 
 * MODO ACTUAL: Reglas (sin costo)
 * MODO FUTURO: IA real (Claude, GPT, etc.)
 * 
 * Para activar IA, cambiar CHATBOT_MODO = 'ia' en config/ia.php
 */

require_once __DIR__ . '/../../config/ia.php';

class InterpretadorIntencion
{
    /**
     * Interpreta un mensaje y devuelve la intención + parámetros
     * 
     * @return array ['intencion' => string, 'parametros' => array]
     */
    public static function interpretar($mensaje)
    {
        if (CHATBOT_MODO === 'ia' && !empty(IA_API_KEY)) {
            return self::interpretarConIA($mensaje);
        }
        return self::interpretarConReglas($mensaje);
    }

    /**
     * Motor de reglas (actual)
     */
    private static function interpretarConReglas($mensaje)
    {
        // Este método usa el Chatbot.php que ya tienes
        // Devuelve: ['intencion' => ..., 'parametros' => [...]]
        return ['intencion' => null, 'parametros' => []];
    }

    /**
     * Motor de IA (futuro)
     */
    private static function interpretarConIA($mensaje)
    {
        $prompt = self::construirPrompt($mensaje);
        
        try {
            if (IA_PROVEEDOR === 'claude') {
                $respuesta = self::llamarClaude($prompt);
            } else {
                $respuesta = self::llamarOpenAI($prompt);
            }
            
            // Parsear JSON de respuesta
            $datos = json_decode($respuesta, true);
            
            if (isset($datos['intencion'])) {
                return [
                    'intencion' => $datos['intencion'],
                    'parametros' => $datos['parametros'] ?? []
                ];
            }
        } catch (Exception $e) {
            // Si falla la IA, caer de vuelta a reglas
            error_log("IA falló: " . $e->getMessage());
        }
        
        return self::interpretarConReglas($mensaje);
    }

    /**
     * Construye el prompt para la IA
     */
    private static function construirPrompt($mensaje)
    {
        return <<<PROMPT
Eres un clasificador de intenciones para un asistente financiero personal.

Tu único trabajo es identificar la intención del usuario y extraer parámetros.
NO respondas al usuario, solo devuelve un JSON.

Intenciones disponibles:
- consultar_patrimonio: "¿cuánto tengo?", "mi capital", "mi dinero total"
- consultar_deudas: "¿cuánto debo?", "mis deudas"
- consultar_balance: "¿cómo van mis finanzas?", "balance del mes"
- consultar_ahorro: "¿cuánto he ahorrado?", "mis metas"
- consultar_cuentas: "mis cuentas", "saldos"
- consultar_me_deben: "¿cuánto me deben?"
- consultar_gasto_categoria: "¿cuánto gasté en X?"
- proyeccion_ahorro: "¿podré ahorrar X en Y meses?"
- proyeccion_deuda: "¿cuándo pago mis deudas?"
- proyeccion_compra: "¿puedo comprar X?"
- recomendacion_inversion: "¿debería invertir?"
- recomendacion_prestamo: "¿me conviene un préstamo?"
- recomendacion_ahorro: "¿cómo ahorro más?"
- recomendacion_deuda: "¿cómo salgo de deudas?"
- educacion_cdt: "¿qué es un CDT?"
- educacion_usura: "¿qué es la tasa de usura?"
- educacion_fondo: "¿qué es fondo de emergencia?"
- crear_gasto: "registra un gasto de X"
- crear_ingreso: "registra un ingreso de X"
- crear_meta: "crea una meta para X"
- crear_deuda: "crea una deuda con X"
- saludo: "hola", "buenos días"
- ayuda: "¿qué puedes hacer?"
- desconocida: no encaja en ninguna

Devuelve SOLO un JSON con esta estructura:
{
  "intencion": "nombre_intencion",
  "parametros": {
    "monto": 5000000,      // si aplica
    "meses": 6,            // si aplica
    "categoria": "comida", // si aplica
    "acreedor": "banco"    // si aplica
  }
}

Mensaje del usuario: "{$mensaje}"

Responde SOLO con el JSON, sin texto adicional.
PROMPT;
    }

    /**
     * Llama a la API de Claude
     */
    private static function llamarClaude($prompt)
    {
        $url = 'https://api.anthropic.com/v1/messages';
        
        $body = [
            'model' => IA_MODELO,
            'max_tokens' => IA_MAX_TOKENS,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . IA_API_KEY,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => IA_TIMEOUT
        ]);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Error API Claude: HTTP $httpCode");
        }

        $datos = json_decode($respuesta, true);
        return $datos['content'][0]['text'] ?? '';
    }

    /**
     * Llama a la API de OpenAI
     */
    private static function llamarOpenAI($prompt)
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $body = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => IA_MAX_TOKENS,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . IA_API_KEY
            ],
            CURLOPT_TIMEOUT => IA_TIMEOUT
        ]);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Error API OpenAI: HTTP $httpCode");
        }

        $datos = json_decode($respuesta, true);
        return $datos['choices'][0]['message']['content'] ?? '';
    }
}