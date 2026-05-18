<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_professor extends CI_Model {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0  - Erro de exceção
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    5  - Professor não cadastrado no sistema
    8  - Houve algum problema de inserção, atualização, consulta ou exclusão
    9  - Professor desativado no sistema
    10 - Professor já cadastrado / Consulta efetuada com sucesso (método privado)
    11 - Professor não encontrado pelo método público
    12 - Professor não encontrado
    98 - Método auxiliar de consulta que não trouxe dados
    */

    /**
     * Insere um novo professor no banco de dados
     */
    public function inserir($nome, $usuario) {
        try {
            // Query de inserção dos dados
            $this->db->query("insert into professores (nome, usuario) 
                              values ('$nome', '$usuario')");

            // Verificar se a inserção ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg'    => 'Professor cadastrado corretamente.'
                );
            } else {
                $dados = array(
                    'codigo' => 8,
                    'msg'    => 'Houve algum problema na inserção na tabela de professor.'
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
     * Consulta professores de acordo com os parâmetros passados
     */
    public function consultar($codigo, $nome, $usuario) {
        try {
            // Query base para consultar dados
            $sql = "select codigo, nome, usuario 
                    from professores where estatus = '' ";

            // Filtros dinâmicos
            if (trim($codigo) != '') {
                $sql = $sql . "and codigo = $codigo ";
            }

            if (trim($nome) != '') {
                $sql = $sql . "and nome like '%$nome%' ";
            }

            if (trim($usuario) != '') {
                $sql = $sql . "and usuario like '%$usuario%' ";
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
                    'msg'    => 'Professor não encontrado.'
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
     * Altera os dados de um professor existente
     */
    public function alterar($codigo, $nome, $usuario)
    {
        try {
            // Verifica se o professor já está cadastrado
            $retornoConsulta = $this->consultaProfessorCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Monta a query dinâmica com Query Bindings (?) para segurança
                $query = "UPDATE professores SET ";
                $updates = [];
                $params = [];

                if ($nome !== '') {
                    $updates[] = "nome = ?";
                    $params[] = $nome;
                }
                if ($usuario !== '') {
                    $updates[] = "usuario = ?";
                    $params[] = $usuario;
                }

                $query .= implode(", ", $updates) . " WHERE codigo = ?";
                $params[] = $codigo;

                // Executa a query passando o array de parâmetros
                $this->db->query($query, $params);

                // Verifica se a atualização foi bem-sucedida
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Professor atualizado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na atualização na tabela de professor.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 5,
                    'msg'    => 'Professor não cadastrado no sistema.'
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
     * Método privado para consultar se um professor existe e seu status
     */
    private function consultaProfessorCod($codigo)
    {
        try {
            // Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from professores where codigo = $codigo ";

            $retornoProfessor = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retornoProfessor->num_rows() > 0) {
                $linha = $retornoProfessor->row();
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg'    => 'Professor desativado no sistema.'
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
                    'msg'    => 'Professor não encontrado.'
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
     * Realiza a exclusão lógica (desativação) do professor
     */
    public function desativar($codigo)
    {
        try {
            // Verifica se o professor já está cadastrado
            $retornoConsulta = $this->consultaProfessorCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Query de atualização dos dados
                $this->db->query("update professores set estatus = 'D'
                                  where codigo = $codigo");

                // Verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Professor DESATIVADO corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na DESATIVAÇÃO do professor.'
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