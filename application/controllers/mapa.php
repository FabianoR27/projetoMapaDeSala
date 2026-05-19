<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mapa extends CI_Controller {

    // Atributos privados da classe
    private $codigo;
    private $codSala;
    private $codProfessor;
    private $codTurma;
    private $codHorario;
    private $status;

    // --- Getters ---
    public function getCodigo() { return $this->codigo; }
    public function getCodSala() { return $this->codSala; }
    public function getCodProfessor() { return $this->codProfessor; }
    public function getCodTurma() { return $this->codTurma; }
    public function getCodHorario() { return $this->codHorario; }
    public function getStatus() { return $this->status; }

    // --- Setters ---
    public function setCodigo($codigoFront) { $this->codigo = $codigoFront; }
    public function setCodSala($codSalaFront) { $this->codSala = $codSalaFront; }
    public function setCodProfessor($codProfessorFront) { $this->codProfessor = $codProfessorFront; }
    public function setCodTurma($codTurmaFront) { $this->codTurma = $codTurmaFront; }
    public function setCodHorario($codHorarioFront) { $this->codHorario = $codHorarioFront; }
    public function setStatus($statusFront) { $this->status = $statusFront; }

    // --- Métodos de Regra de Negócio ---

    public function inserir() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            
            // Padronizado para snake_case
            $lista = [
                "codigo_sala"      => '0',
                "codigo_professor" => '0',
                "codigo_turma"     => '0',
                "codigo_horario"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoSala      = validarDados($resultado->codigo_sala, 'int', true);
                $retornoProfessor = validarDados($resultado->codigo_professor, 'int', true);
                $retornoTurma     = validarDados($resultado->codigo_turma, 'int', true);
                $retornoHorario   = validarDados($resultado->codigo_horario, 'int', true);

                if ($retornoSala['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoSala['codigoHelper'], 'campo' => 'CodigoSala', 'msg' => $retornoSala['msg']];
                }
                if ($retornoProfessor['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoProfessor['codigoHelper'], 'campo' => 'CodigoProfessor', 'msg' => $retornoProfessor['msg']];
                }
                if ($retornoTurma['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoTurma['codigoHelper'], 'campo' => 'CodigoTurma', 'msg' => $retornoTurma['msg']];
                }
                if ($retornoHorario['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoHorario['codigoHelper'], 'campo' => 'CodigoHorario', 'msg' => $retornoHorario['msg']];
                }

                if (empty($erros)) {
                    $this->setCodSala($resultado->codigo_sala);
                    $this->setCodProfessor($resultado->codigo_professor);
                    $this->setCodTurma($resultado->codigo_turma);
                    $this->setCodHorario($resultado->codigo_horario);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->inserir(
                        $this->getCodSala(),
                        $this->getCodProfessor(),
                        $this->getCodTurma(),
                        $this->getCodHorario()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    public function consultar() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            
            $lista = [
                "codigo"           => '0',
                "codigo_sala"      => '0',
                "codigo_professor" => '0',
                "codigo_turma"     => '0',
                "codigo_horario"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoCodigo    = validarDadosConsulta($resultado->codigo, 'int');
                $retornoSala      = validarDadosConsulta($resultado->codigo_sala, 'int');
                $retornoProfessor = validarDadosConsulta($resultado->codigo_professor, 'int');
                $retornoTurma     = validarDadosConsulta($resultado->codigo_turma, 'int');
                $retornoHorario   = validarDadosConsulta($resultado->codigo_horario, 'int');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                }
                if ($retornoSala['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoSala['codigoHelper'], 'campo' => 'CodigoSala', 'msg' => $retornoSala['msg']];
                }
                if ($retornoProfessor['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoProfessor['codigoHelper'], 'campo' => 'CodigoProfessor', 'msg' => $retornoProfessor['msg']];
                }
                if ($retornoTurma['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoTurma['codigoHelper'], 'campo' => 'CodigoTurma', 'msg' => $retornoTurma['msg']];
                }
                if ($retornoHorario['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoHorario['codigoHelper'], 'campo' => 'CodigoHorario', 'msg' => $retornoHorario['msg']];
                }

                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setCodSala($resultado->codigo_sala);
                    $this->setCodProfessor($resultado->codigo_professor);
                    $this->setCodTurma($resultado->codigo_turma);
                    $this->setCodHorario($resultado->codigo_horario);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->consultar(
                        $this->getCodigo(),
                        $this->getCodSala(),
                        $this->getCodProfessor(),
                        $this->getCodTurma(),
                        $this->getCodHorario()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg'], 'dados' => $resBanco['dados']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    public function alterar() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            
            $lista = [
                "codigo"           => '0',
                "codigo_sala"      => '0',
                "codigo_professor" => '0',
                "codigo_turma"     => '0',
                "codigo_horario"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                if (trim($resultado->codigo_sala) == '' && trim($resultado->codigo_professor) == '' && trim($resultado->codigo_turma) == '' && trim($resultado->codigo_horario) == '') {
                    $erros[] = ['codigo' => 12, 'msg' => 'Pelo menos um parâmetro precisa ser passado para atualização'];
                } else {
                    $retornoCodigo    = validarDados($resultado->codigo, 'int', true);
                    $retornoSala      = validarDadosConsulta($resultado->codigo_sala, 'int');
                    $retornoProfessor = validarDadosConsulta($resultado->codigo_professor, 'int');
                    $retornoTurma     = validarDadosConsulta($resultado->codigo_turma, 'int');
                    $retornoHorario   = validarDadosConsulta($resultado->codigo_horario, 'int');

                    if ($retornoCodigo['codigoHelper'] != 1) {
                        $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                    }
                    if ($retornoSala['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoSala['codigoHelper'], 'campo' => 'CodigoSala', 'msg' => $retornoSala['msg']];
                    }
                    if ($retornoProfessor['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoProfessor['codigoHelper'], 'campo' => 'CodigoProfessor', 'msg' => $retornoProfessor['msg']];
                    }
                    if ($retornoTurma['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoTurma['codigoHelper'], 'campo' => 'CodigoTurma', 'msg' => $retornoTurma['msg']];
                    }
                    if ($retornoHorario['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoHorario['codigoHelper'], 'campo' => 'CodigoHorario', 'msg' => $retornoHorario['msg']];
                    }

                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setCodSala($resultado->codigo_sala);
                        $this->setCodProfessor($resultado->codigo_professor);
                        $this->setCodTurma($resultado->codigo_turma);
                        $this->setCodHorario($resultado->codigo_horario);

                        $this->load->model('M_mapa');
                        $resBanco = $this->M_mapa->alterar(
                            $this->getCodigo(),
                            $this->getCodSala(),
                            $this->getCodProfessor(),
                            $this->getCodTurma(),
                            $this->getCodHorario()
                        );

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    public function desativar() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            
            $lista = ["codigo" => '0'];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                }

                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->desativar($this->getCodigo());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }
}