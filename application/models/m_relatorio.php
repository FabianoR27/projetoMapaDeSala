<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class M_relatorio extends CI_Model {
    public function buscarReservaPorData($dataMapa) {
        try {
            $sql = "SELECT
                    DATE_FORMAT(m.dt_reserva, '%d/%m/%Y') AS data_reserva,
                    s.descricao AS desc_sala,
                    s.codigo AS desc_codigo,
                    h.descricao AS desc_periodo,
                    DATE_FORMAT(h.hora_inicial, '%H:%i') AS hora_inicial,
                    DATE_FORMAT(h.hora_final, '%H:%i') AS hora_final,
                    t.descricao AS desc_turma,
                    p.nome AS nome_professor

                    FROM mapas m

                    JOIN professores p ON m.codigo = p.codigo
                    JOIN horarios h ON m.codigo = h.codigo
                    JOIN turmas t ON m.codigo = t.codigo
                    JOIN salas s ON m.codigo = s.codigo

                    WHERE DATE(m.dt_reserva) = '$dataMapa' -- Data corrigida entre aspas
                    AND m.status = '' -- Filtra apenas reservas ativas
                    ORDER BY FIELD(h.descricao, 'Manhã', 'Tarde', 'Noite'), m.sala
                    ";

            $retornoMapa = $this->db->query($sql);

            // Verifica se a consulta retornou resultados
            if ($retornoMapa->num_rows() > 0) {
                $dados = $retornoMapa->result_array(); // Retorna os resultados como um array associativo
            } else {
                $dados = 0; // Retorna 0 se não houver reservas para a data selecionada
            }
        } catch (Exception $e) {
            $dados = [
                'codigo' => 0,
                'msg' => 'Ocorreu um erro ao buscar as reservas: ' . $e->getMessage()
            ];
        }
        return $dados;
    }
}