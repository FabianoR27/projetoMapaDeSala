<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_mapa extends CI_Model {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0  - Erro de exceção
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    5  - Mapa não cadastrado no sistema
    8  - Houve algum problema de inserção, atualização, consulta ou exclusão
    9  - Mapa desativado no sistema
    10 - Mapa já cadastrado / Consulta efetuada com sucesso (método privado)
    11 - Mapa não encontrado pelo método público
    12 - Mapa não encontrado
    98 - Método auxiliar de consulta que não trouxe dados
    */

    /**
     * Insere um novo mapa no banco de dados
     */
    public function inserir($codProfessor, $codTurma, $codHorario) {
        try {
            // Query de inserção dos dados
            $this->db->query("insert into tbl_mapa (codProfessor, codTurma, codHorario) 
                              values ($codProfessor, $codTurma, $codHorario)");

            // Verificar se a inserção ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg'    => 'Mapa cadastrado corretamente.'
                );
            } else {
                $dados = array(
                    'codigo' => 8,
                    'msg'    => 'Houve algum problema na inserção na tabela de mapa.'
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
     * Consulta mapas de acordo com os parâmetros passados
     */
    public function consultar($codigo, $codProfessor, $codTurma, $codHorario) {
        try {
            // Query base para consultar dados
            $sql = "select codigo, codProfessor, codTurma, codHorario 
                    from tbl_mapa where estatus = '' ";

            // Filtros dinâmicos
            if (trim($codigo) != '') {
                $sql = $sql . "and codigo = $codigo ";
            }

            if (trim($codProfessor) != '') {
                $sql = $sql . "and codProfessor = $codProfessor ";
            }

            if (trim($codTurma) != '') {
                $sql = $sql . "and codTurma = $codTurma ";
            }

            if (trim($codHorario) != '') {
                $sql = $sql . "and codHorario = $codHorario ";
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
                    'msg'    => 'Mapa não encontrado.'
                );
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg'    => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        return $dados;
    }

    /**
     * Altera os dados de um mapa existente
     */
    public function alterar($codigo, $codProfessor, $codTurma, $codHorario)
    {
        try {
            // Verifica se o mapa já está cadastrado
            $retornoConsulta = $this->consultaMapaCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Monta a query dinâmica com Query Bindings (?) para segurança e compatibilidade
                $query = "UPDATE tbl_mapa SET ";
                $updates = [];
                $params = [];

                if ($codProfessor !== '') {
                    $updates[] = "codProfessor = ?";
                    $params[] = $codProfessor;
                }
                if ($codTurma !== '') {
                    $updates[] = "codTurma = ?";
                    $params[] = $codTurma;
                }
                if ($codHorario !== '') {
                    $updates[] = "codHorario = ?";
                    $params[] = $codHorario;
                }

                $query .= implode(", ", $updates) . " WHERE codigo = ?";
                $params[] = $codigo;

                // Executa a query passando o array de parâmetros
                $this->db->query($query, $params);

                // Verifica se a atualização foi bem-sucedida
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Mapa atualizado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na atualização na tabela de mapa.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 5,
                    'msg'    => 'Mapa não cadastrado no sistema.'
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
     * Método privado para consultar se um mapa existe e seu status
     */
    private function consultaMapaCod($codigo)
    {
        try {
            // Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_mapa where codigo = $codigo ";

            $retornoMapa = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retornoMapa->num_rows() > 0) {
                $linha = $retornoMapa->row();
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg'    => 'Mapa desativado no sistema.'
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
                    'msg'    => 'Mapa não encontrado.'
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
     * Realiza a exclusão lógica (desativação) do mapa
     */
    public function desativar($codigo)
    {
        try {
            // Verifica se o mapa já está cadastrado
            $retornoConsulta = $this->consultaMapaCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Query de atualização dos dados
                $this->db->query("update tbl_mapa set estatus = 'D'
                                  where codigo = $codigo");

                // Verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Mapa DESATIVADO corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na DESATIVAÇÃO do mapa.'
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
        
        return $dados;
    }
}