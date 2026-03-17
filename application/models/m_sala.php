<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_sala extends CI_Model
{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    8 - Houve algum problema de inserção, atualização, consulta ou exclusão
    9 - Sala desativada no sistema
    10 - Sala já cadastrada
    98 - Método auxiliar de consulta que não trouxe dados
    */

    public function inserir($codigo, $descricao, $andar, $capacidade) {
        try {
            // Verifico se a sala já está cadastrada
            $retornoConsulta = $this->consultaSala($codigo);

            // Se a sala não estiver desativada (9) e não estiver cadastrada (10)
            if ($retornoConsulta['codigo'] != 9 && $retornoConsulta['codigo'] != 10) {
                // Query de inserção dos dados
                $this->db->query("insert into tbl_sala (codigo, descricao, andar, capacidade) 
                                values ($codigo, '$descricao', $andar, $capacidade)");

                // Verificar se a inserção ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Sala cadastrada corretamente'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na inserção na tabela de salas.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        // Envia o array $dados com as informações tratadas
        return $dados;
    }

    // Método privado, pois será auxiliar nesta classe
    private function consultaSala ($codigo){
        try {
            // Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_sala where codigo = $codigo ";
            $retornoSala = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retornoSala->num_rows() > 0) {
                $linha = $retornoSala->row();
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Sala desativada no sistema, caso precise reativar a mesma, fale com o administrador.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 10,
                        'msg' => 'Sala já cadastrada no sistema.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 98,
                    'msg' => 'Sala não encontrada.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        // Envia o array $dados com as informações tratadas
        return $dados;
    }
}
?>