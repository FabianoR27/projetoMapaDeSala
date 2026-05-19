<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_turma extends CI_Model
{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0  - Erro de exceção
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    8  - Houve algum problema de inserção, atualização, consulta ou exclusão
    9  - Turma desativada no sistema
    10 - Turma já cadastrada
    11 - Turma não encontrada pelo método público
    98 - Método auxiliar de consulta que não trouxe dados
    */

    /**
     * Insere uma nova turma no banco de dados
     */
    public function inserir($descricao, $capacidade, $dt_inicio)
    {
        try {
            // Query de inserção dos dados
            $this->db->query("insert into turmas (descricao, capacidade, dt_inicio) 
                              values ('$descricao', $capacidade, '$dt_inicio')");

            // Verificar se a inserção ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg'    => 'Turma cadastrada corretamente.'
                );
            } else {
                $dados = array(
                    'codigo' => 8,
                    'msg'    => 'Houve algum problema na inserção na tabela de turma.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg'    => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Envia o array $dados com as informações tratadas
        return $dados;
    }

    /**
     * Consulta turmas de acordo com os parâmetros passados
     */
    public function consultar($codigo, $descricao, $capacidade, $dt_inicio)
    {
        try {
            // Query base para consultar dados
            $sql = "select codigo, descricao, capacidade, dt_inicio, 
                    date_format(dt_inicio, '%d-%m-%Y') dt_iniciobra 
                    from turmas where status = '' ";

            // Filtros dinâmicos
            if (trim($codigo) != '') {
                $sql = $sql . "and codigo = $codigo ";
            }

            if (trim($descricao) != '') {
                $sql = $sql . "and descricao like '%$descricao%' ";
            }

            if (trim($capacidade) != '') {
                $sql = $sql . "and capacidade = $capacidade ";
            }

            if (trim($dt_inicio) != '') {
                $sql = $sql . "and dt_inicio = '$dt_inicio' ";
            }

            $retorno = $this->db->query($sql);

            // Verificar se a consulta retornou resultados
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg'    => 'Consulta efetuada com sucesso',
                    'dados'  => $retorno->result()
                );
            } else {
                $dados = array(
                    'codigo' => 11,
                    'msg'    => 'Turma não encontrada.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg'    => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Envia o array $dados com as informações tratadas
        return $dados;
    }

    /**
     * Altera os dados de uma turma existente
     */
    public function alterar($codigo, $descricao, $capacidade, $dt_inicio)
    {
        try {
            // Verifica se a turma já está cadastrada
            $retornoConsulta = $this->consultaTurmaCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Monta a query dinâmica
                $query = "UPDATE turmas SET ";
                $updates = [];

                if ($descricao !== '') {
                    $updates[] = "descricao = '$descricao'";
                }
                if ($capacidade !== '') {
                    $updates[] = "capacidade = $capacidade";
                }
                if ($dt_inicio !== '') {
                    $updates[] = "dt_inicio = '$dt_inicio'";
                }

                $query .= implode(", ", $updates) . " WHERE codigo = $codigo ";

                // Prepara os valores para binding
                $params = [];
                if ($descricao !== '') {
                    $params[] = $descricao;
                }
                if ($capacidade !== '') {
                    $params[] = $capacidade;
                }
                if ($dt_inicio !== '') {
                    $params[] = $dt_inicio;
                }
                $params[] = $codigo;

                // Executa a query
                $this->db->query($query, $params);

                // Verifica se a atualização foi bem-sucedida
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Turma atualizada corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na atualização na tabela de turma.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 5,
                    'msg'    => 'Turma não cadastrada no sistema.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg'    => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        return $dados;
    }

    /**
     * Método privado para consultar se uma turma existe e seu status
     */
    private function consultaTurmaCod($codigo)
    {
        try {
            // Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from turmas where codigo = $codigo ";

            $retornoTurma = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retornoTurma->num_rows() > 0) {
                $linha = $retornoTurma->row();
                if (trim($linha->status) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg'    => 'Turma desativada no sistema.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 10,
                        'msg'    => 'Consulta efetuada com sucesso.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 12,
                    'msg'    => 'Turma não encontrada.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg'    => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        
        // Envia o array $dados com as informações tratadas
        // acima pela estrutura de decisão if
        return $dados;
    }

    /**
     * Realiza a exclusão lógica (desativação) da turma
     */
    public function desativar($codigo)
    {
        try {
            // Verifica se a turma já está cadastrada
            $retornoConsulta = $this->consultaTurmaCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Query de atualização dos dados
                $this->db->query("update turmas set status = 'D'
                                  where codigo = $codigo");

                // Verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Turma DESATIVADA corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na DESATIVAÇÃO da turma.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg'    => $retornoConsulta['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg'    => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        
        // Envia o array $dados com as informações tratadas
        // acima pela estrutura de decisão if
        return $dados;
    }
}
