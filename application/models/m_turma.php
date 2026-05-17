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
    public function inserir($descricao, $capacidade, $dataInicio)
    {
        try {
            // Query de inserção dos dados
            $this->db->query("insert into tbl_turma (descricao, capacidade, dataInicio) 
                              values ('$descricao', $capacidade, '$dataInicio')");

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
    public function consultar($codigo, $descricao, $capacidade, $dataInicio)
    {
        try {
            // Query base para consultar dados
            $sql = "select codigo, descricao, capacidade, dataInicio, 
                    date_format(dataInicio, '%d-%m-%Y') dataIniciobra 
                    from tbl_turma where estatus = '' ";

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

            if (trim($dataInicio) != '') {
                $sql = $sql . "and dataInicio = '$dataInicio' ";
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
}
