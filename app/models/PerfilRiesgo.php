<?php
/**
 * Modelo: PerfilRiesgo
 * 
 * Calcula el perfil de riesgo del usuario según sus respuestas
 * en el onboarding.
 */

class PerfilRiesgo
{
    /**
     * Calcula el perfil según las respuestas
     * 
     * @param array $respuestas ['horizonte', 'reaccion', 'objetivo', 'ahorro', 'experiencia']
     * @return array ['perfil' => string, 'puntaje' => int, 'descripcion' => string, 'recomendaciones' => array]
     */
    public static function calcular($respuestas)
    {
        $puntaje = 0;

        // 1. Horizonte (0-25 pts)
        $puntajeHorizonte = [
            'corto' => 5,
            'mediano' => 12,
            'largo' => 20,
            'muy_largo' => 25
        ][$respuestas['horizonte'] ?? 'corto'] ?? 0;

        // 2. Reacción a caída (0-30 pts) — el más importante
        $puntajeReaccion = [
            'vender_todo' => 0,
            'vender_algo' => 8,
            'no_hacer_nada' => 20,
            'comprar_mas' => 30
        ][$respuestas['reaccion'] ?? 'vender_todo'] ?? 0;

        // 3. Objetivo del dinero (0-20 pts)
        $puntajeObjetivo = [
            'preservar' => 5,
            'ingresos' => 10,
            'crecer' => 15,
            'maximizar' => 20
        ][$respuestas['objetivo'] ?? 'preservar'] ?? 0;

        // 4. % de ahorro (0-15 pts)
        $puntajeAhorro = [
            'cero' => 0,
            'bajo' => 5,
            'medio' => 10,
            'alto' => 15
        ][$respuestas['ahorro'] ?? 'cero'] ?? 0;

        // 5. Experiencia (0-10 pts)
        $puntajeExperiencia = [
            'ninguna' => 0,
            'cdt' => 3,
            'fondos' => 6,
            'avanzada' => 10
        ][$respuestas['experiencia'] ?? 'ninguna'] ?? 0;

        $puntaje = $puntajeHorizonte + $puntajeReaccion + $puntajeObjetivo + $puntajeAhorro + $puntajeExperiencia;

        // Clasificar perfil según puntaje total
        if ($puntaje <= 20) {
            $perfil = 'conservador';
        } elseif ($puntaje <= 40) {
            $perfil = 'cauteloso';
        } elseif ($puntaje <= 60) {
            $perfil = 'moderado';
        } elseif ($puntaje <= 80) {
            $perfil = 'valiente';
        } else {
            $perfil = 'arriesgado';
        }

        return [
            'perfil' => $perfil,
            'puntaje' => $puntaje,
            'descripcion' => self::descripcion($perfil),
            'recomendaciones' => self::recomendaciones($perfil),
            'colores' => self::colores($perfil)
        ];
    }

    /**
     * Descripción del perfil
     */
    private static function descripcion($perfil)
    {
        $descripciones = [
            'conservador' => 'Prefieres la seguridad por encima del rendimiento. Los productos de bajo riesgo son ideales para ti.',
            'cauteloso' => 'Buscas un balance entre seguridad y crecimiento. Aceptas algo de riesgo controlado.',
            'moderado' => 'Estás dispuesto a asumir riesgos moderados para obtener mejores rendimientos a largo plazo.',
            'valiente' => 'Buscas maximizar tus rendimientos aceptando volatilidad. Tienes tolerancia a las caídas.',
            'arriesgado' => 'Estás cómodo con alta volatilidad en busca de máximos rendimientos.'
        ];
        return $descripciones[$perfil] ?? '';
    }

    /**
     * Recomendaciones según perfil
     */
    private static function recomendaciones($perfil)
    {
        $recs = [
            'conservador' => [
                'Cuenta de ahorros de alto rendimiento',
                'CDT a corto plazo (3-6 meses)',
                'Fondos de inversión conservadores',
                'Fondo de emergencia primero'
            ],
            'cauteloso' => [
                'CDT a mediano plazo (12 meses)',
                'Fondos de renta fija',
                'Diversificación básica',
                'Mantén 3-6 meses de fondo de emergencia'
            ],
            'moderado' => [
                'FIC con aportes mensuales',
                'CDT a largo plazo',
                'Fondos mixtos (renta fija + variable)',
                'Aportes automáticos mensuales'
            ],
            'valiente' => [
                'Fondos de renta variable',
                'FIC agresivos',
                'Portafolio diversificado',
                'Inversión en bolsa (bajo %)'
            ],
            'arriesgado' => [
                'Acciones individuales',
                'Fondos de inversión agresivos',
                'Criptomonedas (porcentaje menor)',
                'Alta tolerancia a la volatilidad'
            ]
        ];
        return $recs[$perfil] ?? [];
    }

    /**
     * Colores del perfil
     */
    private static function colores($perfil)
    {
        $colores = [
            'conservador' => ['principal' => '#3498db', 'nombre' => 'Conservador', 'emoji' => '🛡️'],
            'cauteloso' => ['principal' => '#2ecc71', 'nombre' => 'Cauteloso', 'emoji' => '🌱'],
            'moderado' => ['principal' => '#f39c12', 'nombre' => 'Moderado', 'emoji' => '⚖️'],
            'valiente' => ['principal' => '#e67e22', 'nombre' => 'Valiente', 'emoji' => '🚀'],
            'arriesgado' => ['principal' => '#e74c3c', 'nombre' => 'Arriesgado', 'emoji' => '🔥']
        ];
        return $colores[$perfil] ?? $colores['moderado'];
    }

    /**
     * Obtiene el perfil del usuario desde la BD
     */
    public static function obtenerPerfil($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare("SELECT perfil_riesgo FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        $r = $stmt->fetch();
        return $r['perfil_riesgo'] ?? null;
    }
}