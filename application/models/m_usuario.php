<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_usuario extends CI_Model
{
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

    public function inserir($nome, $email, $usuario, $senha)
    {
        try {
            // CHECAGEM DE DUPLICIDADE
            $sqlCheck = "SELECT codigo FROM usuarios WHERE usuario = ? OR email = ?";
            $queryCheck = $this->db->query($sqlCheck, [$usuario, $email]);

            if ($queryCheck->num_rows() > 0) {
                // Código 10 - Usuário já cadastrado
                return ['codigo' => 10, 'msg' => 'Usuário já cadastrado no sistema.'];
            }

            // INSERÇÃO
            $senhaMd5 = md5($senha);
            $sqlInsert = "INSERT INTO usuarios (nome, email, usuario, senha) VALUES (?, ?, ?, ?)";
            $this->db->query($sqlInsert, [$nome, $email, $usuario, $senhaMd5]);

            if ($this->db->affected_rows() > 0) {
                // Código 1 - Operação realizada com sucesso
                $dados = ['codigo' => 1, 'msg' => 'Operação realizada no banco de dados com sucesso (Inserção).'];
            } else {
                // Código 8 - Problema na inserção
                $dados = ['codigo' => 8, 'msg' => 'Houve algum problema de inserção.'];
            }
        } catch (Exception $e) {
            // Código 0 - Erro de exceção
            $dados = ['codigo' => 0, 'msg' => 'Erro de exceção: ' . $e->getMessage()];
        }
        return $dados;
    }

    public function consultar($codigo, $nome, $email, $usuario)
    {
        try {
            $sql = "SELECT codigo, nome, email, usuario FROM usuarios WHERE status = '' ";

            if (trim($codigo) != '') {
                $sql .= "AND codigo = $codigo ";
            }
            if (trim($usuario) != '') {
                $sql .= "AND usuario LIKE '%$usuario%' ";
            }
            if (trim($nome) != '') {
                $sql .= "AND nome LIKE '%$nome%' ";
            }
            if (trim($email) != '') {
                $sql .= "AND email LIKE '%$email%' ";
            }

            $retorno = $this->db->query($sql);

            if ($retorno->num_rows() > 0) {
                // Código 1 - Operação realizada com sucesso
                $dados = ['codigo' => 1, 'msg' => 'Operação realizada no banco de dados com sucesso (Consulta).', 'dados' => $retorno->result()];
            } else {
                // Código 11 - Usuário não encontrado pelo método público
                $dados = ['codigo' => 11, 'msg' => 'Usuário não encontrado pelo método público.'];
            }
        } catch (Exception $e) {
            // Código 0 - Erro de exceção
            $dados = ['codigo' => 0, 'msg' => 'Erro de exceção: ' . $e->getMessage()];
        }
        return $dados;
    }

    public function alterar($codigo, $nome, $email, $usuario, $senha)
    {
        try {
            // Valida pelo método privado
            $retornoConsulta = $this->consultaUsuarioCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {

                // CHECAGEM DE DUPLICIDADE NA ATUALIZAÇÃO (Ignora o próprio usuário)
                if (trim($usuario) !== '' || trim($email) !== '') {
                    $sqlCheck = "SELECT codigo FROM usuarios WHERE codigo != ? AND (";
                    $condicoes = [];
                    $paramsCheck = [$codigo];

                    if (trim($usuario) !== '') {
                        $condicoes[] = "usuario = ?";
                        $paramsCheck[] = $usuario;
                    }
                    if (trim($email) !== '') {
                        $condicoes[] = "email = ?";
                        $paramsCheck[] = $email;
                    }

                    $sqlCheck .= implode(" OR ", $condicoes) . ")";
                    $queryCheck = $this->db->query($sqlCheck, $paramsCheck);

                    if ($queryCheck->num_rows() > 0) {
                        // Código 10 - Usuário já cadastrado
                        return ['codigo' => 10, 'msg' => 'Usuário já cadastrado (Este e-mail/usuário pertence a outra conta).'];
                    }
                }

                // UPDATE
                $query = "UPDATE usuarios SET ";
                $updates = [];
                $params = [];

                if (trim($usuario) !== '') {
                    $updates[] = "usuario = ?";
                    $params[] = $usuario;
                }
                if (trim($senha) !== '') {
                    $updates[] = "senha = ?";
                    $params[] = md5($senha);
                }
                if (trim($nome) !== '') {
                    $updates[] = "nome = ?";
                    $params[] = $nome;
                }
                if (trim($email) !== '') {
                    $updates[] = "email = ?";
                    $params[] = $email;
                }

                $query .= implode(", ", $updates) . " WHERE codigo = ?";
                $params[] = $codigo;

                $this->db->query($query, $params);

                if ($this->db->affected_rows() > 0) {
                    // Código 1 - Operação realizada com sucesso
                    $dados = ['codigo' => 1, 'msg' => 'Operação realizada no banco de dados com sucesso (Alteração).'];
                } else {
                    // Código 8 - Problema na atualização
                    $dados = ['codigo' => 8, 'msg' => 'Houve algum problema de atualização (ou nenhum dado foi alterado).'];
                }
            } else {
                // Mapeia o Erro 12 (do método privado) para o Erro 5, como manda a legenda
                if ($retornoConsulta['codigo'] == 12) {
                    // Código 5 - Usuário não cadastrado no sistema
                    $dados = ['codigo' => 5, 'msg' => 'Usuário não cadastrado no sistema.'];
                } else {
                    // Propaga outros erros, como o Código 9 (Desativado)
                    $dados = ['codigo' => $retornoConsulta['codigo'], 'msg' => $retornoConsulta['msg']];
                }
            }
        } catch (Exception $e) {
            // Código 0 - Erro de exceção
            $dados = ['codigo' => 0, 'msg' => 'Erro de exceção: ' . $e->getMessage()];
        }
        return $dados;
    }

    private function consultaUsuarioCod($codigo)
    {
        try {
            $sql = "SELECT * FROM usuarios WHERE codigo = $codigo ";
            $retornoUsuario = $this->db->query($sql);

            if ($retornoUsuario->num_rows() > 0) {
                $linha = $retornoUsuario->row();
                if (trim($linha->status) == "D") {
                    // Código 9 - Usuário desativado no sistema
                    $dados = ['codigo' => 9, 'msg' => 'Usuário desativado no sistema.'];
                } else {
                    // Código 10 - Consulta efetuada com sucesso (método privado)
                    $dados = ['codigo' => 10, 'msg' => 'Consulta efetuada com sucesso (método privado).'];
                }
            } else {
                // Código 12 - Usuário não encontrado
                $dados = ['codigo' => 12, 'msg' => 'Usuário não encontrado.'];
            }
        } catch (Exception $e) {
            // Código 0 - Erro de exceção
            $dados = ['codigo' => 0, 'msg' => 'Erro de exceção: ' . $e->getMessage()];
        }
        return $dados;
    }

    public function desativar($codigo)
    {
        try {
            // Valida pelo método privado
            $retornoConsulta = $this->consultaUsuarioCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                $this->db->query("UPDATE usuarios SET status = 'D' WHERE codigo = $codigo");

                if ($this->db->affected_rows() > 0) {
                    // Código 1 - Operação realizada com sucesso
                    $dados = ['codigo' => 1, 'msg' => 'Usuário desativado com sucesso.'];
                } else {
                    // Código 8 - Problema na exclusão
                    $dados = ['codigo' => 8, 'msg' => 'Houve algum problema de exclusão.'];
                }
            } else {
                // Mapeia o Erro 12 para o Erro 5, igual no alterar()
                if ($retornoConsulta['codigo'] == 12) {
                    // Código 5 - Usuário não cadastrado no sistema
                    $dados = ['codigo' => 5, 'msg' => 'Usuário não cadastrado no sistema.'];
                } else {
                    $dados = ['codigo' => $retornoConsulta['codigo'], 'msg' => $retornoConsulta['msg']];
                }
            }
        } catch (Exception $e) {
            // Código 0 - Erro de exceção
            $dados = ['codigo' => 0, 'msg' => 'Erro de exceção: ' . $e->getMessage()];
        }
        return $dados;
    }

    /**
     * Valida o login do usuário (Transcrito da imagem e adaptado)
     */
    public function validaLogin($usuario, $senha) {
        try {
            // Criptografa a senha para comparar com o banco
            $senhaMd5 = md5($senha);

            // Atributo retorno recebe o resultado do SELECT
            $sql = "SELECT * FROM usuarios WHERE usuario = ? AND senha = ?";
            $retorno = $this->db->query($sql, [$usuario, $senhaMd5]);

            // Verifica se a quantidade de linhas trazidas na consulta é 0
            if ($retorno->num_rows() == 0) {
                $dados = array(
                    'codigo' => 4,
                    'msg'    => 'Usuário ou senha inválidos.'
                );
            } else {
                // Vinculamos o resultado da query para tratarmos o resultado do status
                $linha = $retorno->row();
                
                if (trim($linha->status) == "D") {
                    $dados = array(
                        'codigo' => 5,
                        'msg'    => 'Usuário DESATIVADO NA BASE DE DADOS!'
                    );
                } else {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Usuário correto',
                        'dados'  => $linha
                    );
                }
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
     * Valida se o usuário existe e está ativo (Transcrito da imagem e adaptado)
     */
    public function validaUsuario($usuario) {
        try {
            // Atributo retorno recebe o resultado do SELECT
            $sql = "SELECT * FROM usuarios WHERE usuario = ?";
            $retorno = $this->db->query($sql, [$usuario]);

            // Verifica se a quantidade de linhas trazidas na consulta é 0
            if ($retorno->num_rows() == 0) {
                $dados = array(
                    'codigo' => 4,
                    'msg'    => 'Usuário ou senha inválidos.'
                );
            } else {
                // Vinculamos o resultado da query para tratarmos o resultado do status
                $linha = $retorno->row();
                
                if (trim($linha->status) == "D") {
                    $dados = array(
                        'codigo' => 5,
                        'msg'    => 'Usuário DESATIVADO NA BASE DE DADOS, não pode ser utilizado!'
                    );
                } else {
                    $dados = array(
                        'codigo' => 1,
                        'msg'    => 'Usuário correto'
                    );
                }
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
