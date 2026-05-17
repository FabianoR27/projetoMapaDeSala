<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_usuario extends CI_Model {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0  - Erro de exceção
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    5  - Usuário não cadastrado no sistema
    8  - Houve algum problema de inserção, atualização, consulta ou exclusão
    9  - Usuário desativado no sistema
    10 - Usuário já cadastrado / Consulta efetuada com sucesso (método privado)
    11 - Usuário não encontrado pelo método público
    12 - Usuário não encontrado
    98 - Método auxiliar de consulta que não trouxe dados
    */

    /**
     * Insere um novo usuário no banco de dados
     */
    public function inserir($usuario, $senha, $tipoUsuario) {
        try {
            // Criptografa a senha usando md5
            $senhaMd5 = md5($senha);

            // Query de inserção dos dados
            $this->db->query("insert into tbl_usuario (usuario, senha, tipoUsuario) 
                              values ('$usuario', '$senhaMd5', $tipoUsuario)");

            // Verificar se a inserção ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg'    => 'Usuário cadastrado corretamente.'
                );
            } else {
                $dados = array(
                    'codigo' => 8,
                    'msg'    => 'Houve algum problema na inserção na tabela de usuário.'
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
     * Consulta usuários de acordo com os parâmetros passados
     */
    public function consultar($codigo, $usuario, $tipoUsuario) {
        try {
            // Query base para consultar dados (não traz a senha por segurança)
            $sql = "select codigo, usuario, tipoUsuario 
                    from tbl_usuario where estatus = '' ";

            // Filtros dinâmicos
            if (trim($codigo) != '') {
                $sql = $sql . "and codigo = $codigo ";
            }

            if (trim($usuario) != '') {
                $sql = $sql . "and usuario like '%$usuario%' ";
            }

            if (trim($tipoUsuario) != '') {
                $sql = $sql . "and tipoUsuario = $tipoUsuario ";
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
                    'msg'    => 'Usuário não encontrado.'
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
     * Altera os dados de um usuário existente
     */
    public function alterar($codigo, $usuario, $senha, $tipoUsuario)
    {
        try {
            // Verifica se o usuário já está cadastrado
            $retornoConsulta = $this->consultaUsuarioCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Monta a query dinâmica com Query Bindings (?) para segurança e compatibilidade
                $query = "UPDATE tbl_usuario SET ";
                $updates = [];
                $params = [];

                if ($usuario !== '') {
                    $updates[] = "usuario = ?";
                    $params[] = $usuario;
                }
                if ($senha !== '') {
                    // Se a senha for alterada, aplica o MD5
                    $updates[] = "senha = ?";
                    $params[] = md5($senha);
                }
                if ($tipoUsuario !== '') {
                    $updates[] = "tipoUsuario = ?";
                    $params[] = $tipoUsuario;
                }

                $query .= implode(", ", $updates) . " WHERE codigo = ?";
                $params[] = $codigo;

                // Executa a query passando o array de parâmetros
                $this->db->query($query, $params);

                // Verifica se a atualização foi bem-sucedida
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Usuário atualizado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na atualização na tabela de usuário.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 5,
                    'msg'    => 'Usuário não cadastrado no sistema.'
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
     * Método privado para consultar se um usuário existe e seu status
     */
    private function consultaUsuarioCod($codigo)
    {
        try {
            // Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_usuario where codigo = $codigo ";

            $retornoUsuario = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retornoUsuario->num_rows() > 0) {
                $linha = $retornoUsuario->row();
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg'    => 'Usuário desativado no sistema.'
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
                    'msg'    => 'Usuário não encontrado.'
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
     * Realiza a exclusão lógica (desativação) do usuário
     */
    public function desativar($codigo)
    {
        try {
            // Verifica se o usuário já está cadastrado
            $retornoConsulta = $this->consultaUsuarioCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                // Query de atualização dos dados
                $this->db->query("update tbl_usuario set estatus = 'D'
                                  where codigo = $codigo");

                // Verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Usuário DESATIVADO corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg'    => 'Houve algum problema na DESATIVAÇÃO do usuário.'
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