<?php
defined('BASEPATH') or exit('No direct script access allowed');

class m_horario extends CI_Model {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    8 - Houve algum problema de inserção, atualização, consulta ou exclusão
    9 - Horário desativado no sistema
    10 - Horario já cadastrada
    98 - Método auxiliar de consulta que não trouxe dados
    */

    public function inserir($descricao, $horaInicial, $horaFinal) {
        try {
            // Verifica se o horário já está cadastrada
            $retornoConsulta = $this->consultarHorario('', $horaInicial, $horaFinal);

            // Se o horário não estiver desativada (9) e não estiver cadastrada (10)
            if ($retornoConsulta['codigo'] != 9 && $retornoConsulta['codigo'] != 10) {

                // Query de inserção dos dados
                $this->db->query("insert into horarios (descricao, hora_inicial, hora_final) 
                                values ('$descricao', '$horaInicial', '$horaFinal')");

                // Verificar se a inserção ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Horário cadastrado corretamente'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na inserção na tabela de Horários.'
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
    private function consultarHorario ($codigo, $horaInicial, $horaFinal){
        try {
            // Query para consultar dados de acordo com parâmetros passados
            //$sql = "select * from horarios where codigo = $codigo ";
            if ($codigo != '') {
                $sql = "select * from horarios where codigo = $codigo ";
            } else {
                $sql = "select * from horarios where hora_inicial = '$horaInicial' and hora_final = '$horaFinal' ";
            }
            $retornoHorario = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retornoHorario->num_rows() > 0) {
                $linha = $retornoHorario->row();
                if (trim($linha->status) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Horário desativado no sistema, caso precise reativar, fale com o administrador.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 10,
                        'msg' => 'Horário já cadastrado no sistema.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 98,
                    'msg' => 'Horário não encontrado.'
                );
            }
        } 
        
        catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        // Envia o array $dados com as informações tratadas acima
        return $dados;
    }


    public function consultar ($codigo, $descricao, $horaInicial, $horaFinal) {
        try {
            // Query para consultar dados de acordo com os parâmetros passados
            $sql = "select * from horarios where status = '' ";
            if (trim($codigo) != "") {
                $sql = $sql . "and codigo = $codigo ";
            }

            if (trim($descricao) != '') {
                $sql = $sql . "and descricao like '%$descricao%' ";
            }

            if (trim($horaInicial) != '') {
                $sql = $sql . "and hora_inicial = '$horaInicial' ";
            }

            if (trim($horaFinal) != '') {
                $sql = $sql . "and hora_final = '$horaFinal' ";
            }

            $sql = $sql . "order by codigo";

            $retorno = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta realizada com sucesso.',
                    'dados' => $retorno->result()
                );
            } else {
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Nenhuma Horario encontrada com os parâmetros informados.'
                );
            }
        }

        catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Envia o array $dados com as informações tratadas acima pela estrutura de decisão 'if'
        return $dados;
    }

    // método para montar a Horario de forma dinâmica, ou seja, de acordo com os parâmetros passados pela controller
    public function alterar ($codigo, $descricao, $horaInicial, $horaFinal) {
        try {
            // verifica se a Horario já existe no sistema
            $retornoConsulta = $this->consultarHorario($codigo, '', '', '');

            if ($retornoConsulta['codigo'] == 10) {
                // início dda query de atualização dos dados
                $query = "update horarios set ";

                // comparando os itens para montar a query de forma dinâmica
                if ($descricao !== '') {
                    $query .=  "descricao = '$descricao', ";
                }

                if ($horaInicial !== '') {
                    $query .= "hora_inicial = '$horaInicial', ";
                }

                if ($horaFinal !== '') {
                    $query .= "hora_final = '$horaFinal', ";
                }

                // término da concatenação da query, retirando a última vírgula e adicionando a cláusula where
                $queryFinal = rtrim($query, ', ') . " where codigo = $codigo";

                //execução da query de atualização
                $this->db->query($queryFinal);

                // verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array (
                        'codigo' => 1,
                        'msg' => 'Horario atualizada corretamente.'
                    );
                } else {
                    $dados = array (
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na atualização da tabela de Horarios.'
                    );
                }
            } else {
                $dados = array (
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        }

        catch (Exception $e) {
            $dados = array (
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Envia o array $dados com as informações tratadas acima pela estrutura de decisão 'if'
        return $dados;
    }

    // método para desativar a Horario, ou seja, não excluir a Horario do banco de dados, apenas marcar a mesma como desativada
    public function desativar ($codigo) {
        try {
            // verifica se a Horario já existe no sistema
            $retornoConsulta = $this->consultarHorario($codigo, '', '');

            if ($retornoConsulta['codigo'] == 10) {
                // query para desativar a Horario
                $this->db->query("update horarios set status = 'D' where codigo = $codigo");

                // verificar se a desativação ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array (
                        'codigo' => 1,
                        'msg' => 'Horário desativado corretamente.'
                    );
                } else {
                    $dados = array (
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na desativação do Horário.'
                    );
                }
            } else {
                $dados = array (
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        }
        catch (Exception $e) {
            $dados = array (
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        // Envia o array $dados com as informações tratadas acima pela estrutura de decisão 'if'
        return $dados;
    }
}
?>