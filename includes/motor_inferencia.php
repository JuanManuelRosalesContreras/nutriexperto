<?php
require_once 'db.php';

class MotorInferencia {

    private $conn;
    private $alimentosSeleccionados = [];
    private $categorias = [];
    private $recetasRecomendadas = [];
    private $recomendaciones = [];
    private $puntuacionSalud = 0;

    public function __construct() {
        $this->conn = conectar();
    }

    // ─── ENTRADA PRINCIPAL ───────────────────────────────────────────
    public function inferir($idsAlimentos, $esPremium = false) {
        $this->alimentosSeleccionados = $this->obtenerAlimentos($idsAlimentos);
        $this->calcularCategorias();
        $this->calcularPuntuacionSalud();
        $this->aplicarReglas($esPremium);
        $this->generarRecomendaciones();

        return [
            'recetas'          => $this->recetasRecomendadas,
            'recomendaciones'  => $this->recomendaciones,
            'puntuacion_salud' => $this->puntuacionSalud,
            'categorias'       => $this->categorias,
            'alimentos'        => $this->alimentosSeleccionados
        ];
    }

    // ─── OBTENER DATOS DE ALIMENTOS SELECCIONADOS ────────────────────
    private function obtenerAlimentos($ids) {
        if (empty($ids)) return [];

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $tipos = str_repeat('i', count($ids));

        $stmt = $this->conn->prepare(
            "SELECT * FROM alimentos WHERE id IN ($placeholders) AND activo = 1"
        );
        $stmt->bind_param($tipos, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $alimentos = [];
        while ($row = $result->fetch_assoc()) {
            $alimentos[] = $row;
        }
        return $alimentos;
    }

    // ─── CALCULAR QUÉ CATEGORÍAS TIENE EL USUARIO ───────────────────
    private function calcularCategorias() {
        $this->categorias = [];
        foreach ($this->alimentosSeleccionados as $alimento) {
            $cat = $alimento['categoria'];
            if (!isset($this->categorias[$cat])) {
                $this->categorias[$cat] = 0;
            }
            $this->categorias[$cat]++;
        }
    }

    // ─── PUNTUACIÓN DE SALUD (0-100) ─────────────────────────────────
    private function calcularPuntuacionSalud() {
        $puntos = 0;
        $cats   = $this->categorias;

        // Regla 1: Tiene proteína
        if (isset($cats['proteina']))        $puntos += 25;
        // Regla 2: Tiene verdura
        if (isset($cats['verdura']))         $puntos += 25;
        // Regla 3: Tiene fruta
        if (isset($cats['fruta']))           $puntos += 15;
        // Regla 4: Tiene cereal
        if (isset($cats['cereal']))          $puntos += 15;
        // Regla 5: Tiene grasa saludable
        if (isset($cats['grasa_saludable'])) $puntos += 10;
        // Regla 6: Tiene lácteo
        if (isset($cats['lacteo']))          $puntos += 10;
        // Bonus: más de 3 categorías distintas
        if (count($cats) >= 3)               $puntos += 10;
        // Bonus: seleccionó 5 o más alimentos
        if (count($this->alimentosSeleccionados) >= 5) $puntos += 10;
        // Penalización: solo eligió cereales o solo proteínas
        if (count($cats) === 1)              $puntos -= 20;

        $this->puntuacionSalud = max(0, min(100, $puntos));
    }

    // ─── MOTOR DE REGLAS PRINCIPAL ───────────────────────────────────
    private function aplicarReglas($esPremium) {
        $idsSeleccionados = array_column($this->alimentosSeleccionados, 'id');
        $cats = array_keys($this->categorias);

        // Obtener todas las recetas con sus ingredientes
        $recetas = $this->obtenerRecetasConIngredientes($esPremium);

        foreach ($recetas as $receta) {
            $score = $this->calcularScoreReceta($receta, $idsSeleccionados, $cats);
            if ($score > 0) {
                $receta['score']        = $score;
                $this->recetasRecomendadas[] = $receta;
            }
        }

        // Ordenar por score descendente
        usort($this->recetasRecomendadas, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Máximo 6 recetas
        $this->recetasRecomendadas = array_slice($this->recetasRecomendadas, 0, 6);
    }

    // ─── OBTENER RECETAS CON SUS INGREDIENTES ────────────────────────
    private function obtenerRecetasConIngredientes($esPremium) {
        $filtro = $esPremium ? '' : 'AND r.es_premium = 0';

        $sql = "
            SELECT r.*,
                   GROUP_CONCAT(ra.alimento_id ORDER BY ra.es_principal DESC) AS ingredientes_ids,
                   GROUP_CONCAT(ra.es_principal ORDER BY ra.es_principal DESC) AS ingredientes_principal
            FROM recetas r
            JOIN receta_alimentos ra ON r.id = ra.receta_id
            WHERE r.activo = 1 $filtro
            GROUP BY r.id
        ";

        $result = $this->conn->query($sql);
        $recetas = [];
        while ($row = $result->fetch_assoc()) {
            $row['ingredientes_ids']       = explode(',', $row['ingredientes_ids']);
            $row['ingredientes_principal'] = explode(',', $row['ingredientes_principal']);
            $recetas[] = $row;
        }
        return $recetas;
    }

    // ─── CALCULAR QUÉ TAN COMPATIBLE ES UNA RECETA ───────────────────
    private function calcularScoreReceta($receta, $idsSeleccionados, $cats) {
        $score = 0;
        $ingredientesReceta    = $receta['ingredientes_ids'];
        $ingredientesPrincipal = $receta['ingredientes_principal'];

        foreach ($ingredientesReceta as $i => $ingId) {
            if (in_array($ingId, $idsSeleccionados)) {
                // Ingrediente principal tiene más peso
                $esPrincipal = isset($ingredientesPrincipal[$i]) 
                               && $ingredientesPrincipal[$i] == 1;
                $score += $esPrincipal ? 3 : 1;
            }
        }

        // Bonus si la receta tiene al menos 2 ingredientes del usuario
        $coincidencias = count(array_intersect($ingredientesReceta, $idsSeleccionados));
        if ($coincidencias >= 2) $score += 5;
        if ($coincidencias >= 4) $score += 10;

        // Solo mostrar si hay al menos 1 coincidencia
        return $coincidencias >= 1 ? $score : 0;
    }

    // ─── REGLAS DE RECOMENDACIONES NUTRICIONALES ─────────────────────
    private function generarRecomendaciones() {
        $cats  = array_keys($this->categorias);
        $recs  = [];
        $punt  = $this->puntuacionSalud;

        // Reglas por categorías faltantes
        if (!in_array('proteina', $cats)) {
            $recs[] = [
                'tipo'    => 'advertencia',
                'icono'   => '⚠️',
                'mensaje' => 'No seleccionaste ninguna fuente de proteína. Agrega pollo, huevo, frijoles o atún para una comida más completa.'
            ];
        }

        if (!in_array('verdura', $cats)) {
            $recs[] = [
                'tipo'    => 'advertencia',
                'icono'   => '🥦',
                'mensaje' => 'Faltan verduras en tu selección. Los nopales, calabacitas o jitomate aportan fibra y vitaminas esenciales.'
            ];
        }

        if (!in_array('cereal', $cats)) {
            $recs[] = [
                'tipo'    => 'info',
                'icono'   => '🌽',
                'mensaje' => 'Considera agregar tortilla de maíz o arroz para obtener energía de carbohidratos complejos.'
            ];
        }

        if (!in_array('fruta', $cats)) {
            $recs[] = [
                'tipo'    => 'info',
                'icono'   => '🍊',
                'mensaje' => 'Incluye fruta como naranja, guayaba o papaya para obtener vitamina C y antioxidantes.'
            ];
        }

        if (!in_array('grasa_saludable', $cats)) {
            $recs[] = [
                'tipo'    => 'info',
                'icono'   => '🥑',
                'mensaje' => 'El aguacate o los cacahuates aportan grasas saludables que protegen el corazón.'
            ];
        }

        // Reglas por combinaciones especiales
        if (isset($this->categorias['proteina']) && isset($this->categorias['verdura'])) {
            $recs[] = [
                'tipo'    => 'exito',
                'icono'   => '✅',
                'mensaje' => '¡Excelente combinación! Proteína + verduras es la base de una alimentación saludable.'
            ];
        }

        if (isset($this->categorias['cereal']) && isset($this->categorias['proteina'])) {
            $recs[] = [
                'tipo'    => 'exito',
                'icono'   => '💪',
                'mensaje' => 'Buena combinación de energía y proteína. Ideal para mantener el nivel de actividad durante el día.'
            ];
        }

        // Reglas por puntuación
        if ($punt >= 80) {
            $recs[] = [
                'tipo'    => 'exito',
                'icono'   => '🌟',
                'mensaje' => '¡Selección muy equilibrada! Tu combinación de alimentos cubre los principales grupos nutricionales.'
            ];
        } elseif ($punt >= 50) {
            $recs[] = [
                'tipo'    => 'info',
                'icono'   => '📊',
                'mensaje' => 'Tu selección es moderadamente saludable. Intenta agregar más variedad de grupos alimenticios.'
            ];
        } else {
            $recs[] = [
                'tipo'    => 'advertencia',
                'icono'   => '📉',
                'mensaje' => 'Tu selección tiene poca variedad nutricional. Intenta incluir alimentos de al menos 3 grupos distintos.'
            ];
        }

        // Reglas específicas de alimentos mexicanos
        $ids = array_column($this->alimentosSeleccionados, 'id');

        if (in_array(19, $ids)) { // Nopal
            $recs[] = [
                'tipo'    => 'exito',
                'icono'   => '🌵',
                'mensaje' => '¡El nopal es un superalimento mexicano! Ayuda a controlar el azúcar en sangre y es rico en fibra.'
            ];
        }

        if (in_array(28, $ids)) { // Aguacate
            $recs[] = [
                'tipo'    => 'exito',
                'icono'   => '🥑',
                'mensaje' => 'El aguacate aporta ácido oleico, excelente para la salud cardiovascular.'
            ];
        }

        if (in_array(5, $ids) || in_array(6, $ids)) { // Frijoles
            $recs[] = [
                'tipo'    => 'exito',
                'icono'   => '🫘',
                'mensaje' => 'Los frijoles son proteína vegetal completa y parte fundamental de la dieta mexicana saludable.'
            ];
        }

        if (in_array(41, $ids)) { // Tortilla de maíz
            $recs[] = [
                'tipo'    => 'info',
                'icono'   => '🫓',
                'mensaje' => 'La tortilla de maíz nixtamalizada aporta calcio y es libre de gluten. Prefiere las de maíz sobre las de harina.'
            ];
        }

        $this->recomendaciones = $recs;
    }

    // ─── GUARDAR CONSULTA EN HISTORIAL ───────────────────────────────
    public function guardarConsulta($idsAlimentos, $resultado, $usuarioId = null) {
        $conn = $this->conn;

        $alimentosJson = json_encode($idsAlimentos);
        $recetasJson   = json_encode(array_column($resultado['recetas'], 'id'));
        $puntuacion    = $resultado['puntuacion_salud'];

        $stmt = $conn->prepare("
            INSERT INTO historial_consultas 
            (usuario_id, alimentos_seleccionados, recetas_recomendadas, puntuacion_salud)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('issi', $usuarioId, $alimentosJson, $recetasJson, $puntuacion);
        $stmt->execute();

        // Actualizar Data Warehouse
        $this->actualizarDataWarehouse($idsAlimentos, $resultado['recetas']);

        return $conn->insert_id;
    }

    // ─── ACTUALIZAR DATA WAREHOUSE ───────────────────────────────────
    private function actualizarDataWarehouse($idsAlimentos, $recetas) {
        $hoy = date('Y-m-d');

        // Actualizar alimentos populares
        foreach ($idsAlimentos as $id) {
            $id = intval($id);
            $this->conn->query("
                INSERT INTO dw_alimentos_populares (alimento_id, total_selecciones, fecha_actualizacion)
                VALUES ($id, 1, '$hoy')
                ON DUPLICATE KEY UPDATE 
                total_selecciones = total_selecciones + 1,
                fecha_actualizacion = '$hoy'
            ");
        }

        // Actualizar recetas populares
        foreach ($recetas as $receta) {
            $id = intval($receta['id']);
            $this->conn->query("
                INSERT INTO dw_recetas_populares (receta_id, total_recomendaciones, fecha_actualizacion)
                VALUES ($id, 1, '$hoy')
                ON DUPLICATE KEY UPDATE 
                total_recomendaciones = total_recomendaciones + 1,
                fecha_actualizacion = '$hoy'
            ");
        }
    }
}
?>