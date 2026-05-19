<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_mapa extends CI_Model {

    public function inserir($codSala, $codProfessor, $codTurma, $codHorario) {
        try {
            $this->db->query("insert into mapas (codigo_sala, codigo_professor, codigo_turma, codigo_horario) 
                              values ($codSala, $codProfessor, $codTurma, $codHorario)");

            if ($this->db->affected_rows() > 0) {
                $dados = ['codigo' => 1, 'msg' => 'Mapa cadastrado corretamente.'];
            } else {
                $dados = ['codigo' => 8, 'msg' => 'Houve algum problema na inserção na tabela de mapa.'];
            }
        } catch (Exception $e) {
            $dados = ['codigo' => 0, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()];
        }
        return $dados;
    }

    public function consultar($codigo, $codSala, $codProfessor, $codTurma, $codHorario) {
        try {
            $sql = "select codigo, codigo_sala, codigo_professor, codigo_turma, codigo_horario 
                    from mapas where status = '' ";

            if (trim($codigo) != '') {
                $sql .= "and codigo = $codigo ";
            }
            if (trim($codSala) != '') {
                $sql .= "and codigo_sala = $codSala ";
            }
            if (trim($codProfessor) != '') {
                $sql .= "and codigo_professor = $codProfessor ";
            }
            if (trim($codTurma) != '') {
                $sql .= "and codigo_turma = $codTurma ";
            }
            if (trim($codHorario) != '') {
                $sql .= "and codigo_horario = $codHorario ";
            }

            $retorno = $this->db->query($sql);

            if ($retorno->num_rows() > 0) {
                $dados = ['codigo' => 1, 'msg' => 'Consulta efetuada com sucesso', 'dados' => $retorno->result()];
            } else {
                $dados = ['codigo' => 11, 'msg' => 'Mapa não encontrado.'];
            }
        } catch (Exception $e) {
            $dados = ['codigo' => 0, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()];
        }
        return $dados;
    }

    public function alterar($codigo, $codSala, $codProfessor, $codTurma, $codHorario) {
        try {
            $retornoConsulta = $this->consultaMapaCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                $query = "UPDATE mapas SET ";
                $updates = [];
                $params = [];

                if (trim($codSala) !== '') {
                    $updates[] = "codigo_sala = ?";
                    $params[] = $codSala;
                }
                if (trim($codProfessor) !== '') {
                    $updates[] = "codigo_professor = ?";
                    $params[] = $codProfessor;
                }  
                if (trim($codTurma) !== '') {
                    $updates[] = "codigo_turma = ?";
                    $params[] = $codTurma;
                }
                if (trim($codHorario) !== '') {
                    $updates[] = "codigo_horario = ?";
                    $params[] = $codHorario;
                }

                $query .= implode(", ", $updates) . " WHERE codigo = ?";
                $params[] = $codigo;

                $this->db->query($query, $params);

                if ($this->db->affected_rows() > 0) {
                    $dados = ['codigo' => 1, 'msg' => 'Mapa atualizado corretamente.'];
                } else {
                    $dados = ['codigo' => 8, 'msg' => 'Houve algum problema na atualização na tabela de mapa (ou nenhum dado foi alterado).'];
                }
            } else {
                $dados = ['codigo' => 5, 'msg' => 'Mapa não cadastrado no sistema.'];
            }
        } catch (Exception $e) {
            $dados = ['codigo' => 00, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()];
        }
        return $dados;
    }

    private function consultaMapaCod($codigo) {
        try {
            $sql = "select * from mapas where codigo = $codigo ";
            $retornoMapa = $this->db->query($sql);

            if ($retornoMapa->num_rows() > 0) {
                $linha = $retornoMapa->row();
                if (trim($linha->status) == "D") {
                    $dados = ['codigo' => 9, 'msg' => 'Mapa desativado no sistema.'];
                } else {
                    $dados = ['codigo' => 10, 'msg' => 'Consulta efetuada com sucesso.'];
                }
            } else {
                $dados = ['codigo' => 12, 'msg' => 'Mapa não encontrado.'];
            }
        } catch (Exception $e) {
            $dados = ['codigo' => 00, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()];
        }
        return $dados;
    }

    public function desativar($codigo) {
        try {
            $retornoConsulta = $this->consultaMapaCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                $this->db->query("update mapas set status = 'D' where codigo = $codigo");

                if ($this->db->affected_rows() > 0) {
                    $dados = ['codigo' => 1, 'msg' => 'Mapa DESATIVADO corretamente.'];
                } else {
                    $dados = ['codigo' => 8, 'msg' => 'Houve algum problema na DESATIVAÇÃO do mapa.'];
                }
            } else {
                $dados = ['codigo' => $retornoConsulta['codigo'], 'msg' => $retornoConsulta['msg']];
            }
        } catch (Exception $e) {
            $dados = ['codigo' => 00, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()];
        }
        return $dados;
    }
}