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
    13 - CPF já cadastrado no sistema
    98 - Método auxiliar de consulta que não trouxe dados
    */

    /**
     * Insere um novo professor no banco de dados
     */
    public function inserir($nome, $cpf, $tipo) {
        try {
            // Verifica se o CPF já existe no banco antes de inserir
            $checkCpf = $this->consultar('', '', $cpf, '');
            if ($checkCpf['codigo'] == 1) {
                return array(
                    'codigo' => 13,
                    'msg'    => 'O CPF informado já está cadastrado no sistema.'
                );
            }

            // Query de inserção dos dados com Query Bindings para segurança
            $sql = "INSERT INTO professores (nome, cpf, tipo) VALUES (?, ?, ?)";
            $this->db->query($sql, array($nome, $cpf, $tipo));

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
    public function consultar($codigo, $nome, $cpf, $tipo) {
        try {
            // Query base para consultar dados
            $sql = "SELECT codigo, nome, cpf, tipo 
                    FROM professores WHERE status = '' ";

            // Filtros dinâmicos
            if (trim($codigo) != '') {
                $sql .= "AND codigo = $codigo ";
            }

            if (trim($nome) != '') {
                $sql .= "AND nome LIKE '%$nome%' ";
            }

            if (trim($cpf) != '') {
                $sql .= "AND cpf LIKE '%$cpf%' ";
            }

            if (trim($tipo) != '') {
                $sql .= "AND tipo LIKE '%$tipo%' ";
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
    public function alterar($codigo, $nome, $cpf, $tipo)
    {
        try {
            // Verifica se o professor já está cadastrado
            $retornoConsulta = $this->consultar($codigo, '', '', '');

            // Ajustado para '1' pois é o retorno de sucesso do método consultar público
            if ($retornoConsulta['codigo'] == 1) { 
                
                // Se o CPF estiver sendo alterado, verifica se não pertence a outro professor
                if ($cpf !== '') {
                    $checkCpf = $this->consultar('', '', $cpf, '');
                    // Se achou o CPF, mas o código do professor dono do CPF for diferente do que está sendo editado
                    if ($checkCpf['codigo'] == 1 && $checkCpf['dados'][0]->codigo != $codigo) {
                        return array(
                            'codigo' => 13,
                            'msg'    => 'O CPF informado já está cadastrado para outro professor.'
                        );
                    }
                }

                // Monta a query dinâmica com Query Bindings (?) para segurança
                $query = "UPDATE professores SET ";
                $updates = [];
                $params = [];

                if ($nome !== '') {
                    $updates[] = "nome = ?";
                    $params[] = $nome;
                }
                if ($cpf !== '') {
                    $updates[] = "cpf = ?";
                    $params[] = $cpf;
                }
                if ($tipo !== '') {
                    $updates[] = "tipo = ?";
                    $params[] = $tipo;
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
                        'msg'    => 'Nenhum dado foi alterado ou houve um problema na atualização.'
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
     * Realiza a exclusão lógica (desativação) do professor
     */
    public function desativar($codigo)
    {
        try {
            // Verifica se o professor já está cadastrado
            $retornoConsulta = $this->consultar($codigo, '', '', '');

            // Ajustado para '1' pois é o retorno de sucesso do método consultar público
            if ($retornoConsulta['codigo'] == 1) { 
                // Query de atualização dos dados
                $this->db->query("UPDATE professores SET status = 'D' WHERE codigo = $codigo");

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