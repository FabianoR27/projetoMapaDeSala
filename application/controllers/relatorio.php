<?php
defined ('BASEPATH') OR exit('No direct script access allowed');

class Relatorio extends CI_Controller {

    public function gerarRelatorio() {
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            if (empty($resultado ->dataMapa)) {
                echo json_encode([
                    'codigo' => 2,
                    'msg' => 'Data não informada. Por favor, selecione uma data para gerar o relatório.'
                ]);
                return;
            }

            $this->load->model('M_relatorio'); // Carrega o modelo de relatório

            $dados = $this->M_relatorio->buscarReservaPorData($resultado ->dataMapa); // Chama a função para gerar o relatório

            if (empty($dados) && $dados != 0) {
                echo json_encode([
                    'codigo' => 1,
                    'msg' => 'Relatório gerado com sucesso!',
                    'dados' => $dados // Retorna os dados do relatório
                ]);
            } else {
                echo json_encode([
                    'codigo' => 3,
                    'msg' => 'Nenhuma reserva encontrada para a data selecionada.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'codigo' => 0,
                'msg' => 'Ocorreu um erro ao gerar o relatório: ' . $e->getMessage()
            ]);
        }
    }
}