<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Turma extends CI_Controller {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2  - Conteúdo passado nulo ou vazio
    3  - Conteúdo zerado
    4  - Conteúdo não inteiro
    5  - Conteúdo não é um texto
    6  - Data em formato inválido
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros passados do front não correspondem ao método
    */

    // Atributos privados da classe
    private $codigo;
    private $descricao;
    private $capacidade;
    private $dataInicio;
    private $estatus;

    // --- Getters ---

    public function getCodigo() {
        return $this->codigo;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getCapacidade() {
        return $this->capacidade;
    }

    public function getDataInicio() {
        return $this->dataInicio;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    // --- Setters ---

    public function setCodigo($codigoFront) {
        $this->codigo = $codigoFront;
    }

    public function setDescricao($descricaoFront) {
        $this->descricao = $descricaoFront;
    }

    public function setCapacidade($capacidadeFront) {
        $this->capacidade = $capacidadeFront;
    }

    public function setDataInicio($dataInicioFront) {
        $this->dataInicio = $dataInicioFront;
    }

    public function setEstatus($estatusFront) {
        $this->estatus = $estatusFront;
    }

    // --- Métodos de Regra de Negócio ---

    /**
     * Insere uma nova turma
     */
    public function inserir() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = [
                "descricao"  => '0',
                "capacidade" => '0',
                "dataInicio" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = [
                    'codigo' => 99, 
                    'msg'    => 'Campos inexistentes ou incorretos no FrontEnd.'
                ];
            } else {
                // Validar campos quanto ao tipo e tamanho (Helper)
                $retornoDescricao  = validarDados($resultado->descricao, 'string', true);
                $retornoCapacidade = validarDados($resultado->capacidade, 'int', true);
                $retornoDataInicio = validarDados($resultado->dataInicio, 'date', true);

                if ($retornoDescricao['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'],
                        'campo'  => 'Descrição',
                        'msg'    => $retornoDescricao['msg']
                    ];
                }

                if ($retornoCapacidade['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCapacidade['codigoHelper'],
                        'campo'  => 'Capacidade',
                        'msg'    => $retornoCapacidade['msg']
                    ];
                }

                if ($retornoDataInicio['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoDataInicio['codigoHelper'],
                        'campo'  => 'Andar',
                        'msg'    => $retornoDataInicio['msg']
                    ];
                }

                // Se não encontrar erros, procede com a inserção
                if (empty($erros)) {
                    $this->setDescricao($resultado->descricao);
                    $this->setCapacidade($resultado->capacidade);
                    $this->setDataInicio($resultado->dataInicio);

                    $this->load->model('M_turma');
                    $resBanco = $this->M_turma->inserir(
                        $this->getDescricao(),
                        $this->getCapacidade(),
                        $this->getDataInicio()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Retorno JSON
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        echo json_encode($retorno);
    }

    /**
     * Consulta turmas
     */
    public function consultar() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = [
                "codigo"     => '0',
                "descricao"  => '0',
                "capacidade" => '0',
                "dataInicio" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = [
                    'codigo' => 99, 
                    'msg'    => 'Campos inexistentes ou incorretos no FrontEnd.'
                ];
            } else {
                $retornoCodigo     = validarDadosConsulta($resultado->codigo, 'int');
                $retornoDescricao  = validarDadosConsulta($resultado->descricao, 'string');
                $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int');
                $retornoDataInicio = validarDadosConsulta($resultado->dataInicio, 'date');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];
                }
                
                // (Repetição da lógica de validação de erros para Descrição, Capacidade e DataInicio semelhante ao inserir)
                if ($retornoDescricao['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoDescricao['codigoHelper'], 'campo' => 'Descrição', 'msg' => $retornoDescricao['msg']];
                }
                if ($retornoCapacidade['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'], 'campo' => 'Capacidade', 'msg' => $retornoCapacidade['msg']];
                }
                if ($retornoDataInicio['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoDataInicio['codigoHelper'], 'campo' => 'Andar', 'msg' => $retornoDataInicio['msg']];
                }

                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setDescricao($resultado->descricao);
                    $this->setCapacidade($resultado->capacidade);
                    $this->setDataInicio($resultado->dataInicio);

                    $this->load->model('M_turma');
                    $resBanco = $this->M_turma->consultar(
                        $this->getCodigo(),
                        $this->getDescricao(),
                        $this->getCapacidade(),
                        $this->getDataInicio()
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

        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg'],
                'dados'   => $resBanco['dados']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        echo json_encode($retorno);
    }
}