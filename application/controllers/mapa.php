<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mapa extends CI_Controller {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2  - Conteúdo passado nulo ou vazio
    3  - Conteúdo zerado
    4  - Conteúdo não inteiro
    5  - Conteúdo não é um texto
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros passados do front não correspondem ao método
    */

    // Atributos privados da classe
    private $codigo;
    private $codProfessor;
    private $codTurma;
    private $codHorario;
    private $estatus;

    // --- Getters dos atributos ---

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getCodProfessor()
    {
        return $this->codProfessor;
    }

    public function getCodTurma()
    {
        return $this->codTurma;
    }

    public function getCodHorario()
    {
        return $this->codHorario;
    }

    public function getEstatus()
    {
        return $this->estatus;
    }

    // --- Setters dos atributos ---

    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }

    public function setCodProfessor($codProfessorFront)
    {
        $this->codProfessor = $codProfessorFront;
    }

    public function setCodTurma($codTurmaFront)
    {
        $this->codTurma = $codTurmaFront;
    }

    public function setCodHorario($codHorarioFront)
    {
        $this->codHorario = $codHorarioFront;
    }

    public function setEstatus($estatusFront)
    {
        $this->estatus = $estatusFront;
    }

    // --- Métodos de Regra de Negócio ---

    /**
     * Insere um novo mapa de alocação
     */
    public function inserir() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codProfessor" => '0',
                "codTurma"     => '0',
                "codHorario"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoProfessor = validarDados($resultado->codProfessor, 'int', true);
                $retornoTurma     = validarDados($resultado->codTurma, 'int', true);
                $retornoHorario   = validarDados($resultado->codHorario, 'int', true);

                if ($retornoProfessor['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoProfessor['codigoHelper'],
                        'campo'  => 'CodProfessor',
                        'msg'    => $retornoProfessor['msg']
                    ];
                }

                if ($retornoTurma['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoTurma['codigoHelper'],
                        'campo'  => 'CodTurma',
                        'msg'    => $retornoTurma['msg']
                    ];
                }

                if ($retornoHorario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoHorario['codigoHelper'],
                        'campo'  => 'CodHorario',
                        'msg'    => $retornoHorario['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodProfessor($resultado->codProfessor);
                    $this->setCodTurma($resultado->codTurma);
                    $this->setCodHorario($resultado->codHorario);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->inserir(
                        $this->getCodProfessor(),
                        $this->getCodTurma(),
                        $this->getCodHorario()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
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

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    /**
     * Consulta os mapas cadastrados
     */
    public function consultar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo"       => '0',
                "codProfessor" => '0',
                "codTurma"     => '0',
                "codHorario"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo    = validarDadosConsulta($resultado->codigo, 'int');
                $retornoProfessor = validarDadosConsulta($resultado->codProfessor, 'int');
                $retornoTurma     = validarDadosConsulta($resultado->codTurma, 'int');
                $retornoHorario   = validarDadosConsulta($resultado->codHorario, 'int');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];
                }

                if ($retornoProfessor['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoProfessor['codigoHelper'],
                        'campo'  => 'CodProfessor',
                        'msg'    => $retornoProfessor['msg']
                    ];
                }

                if ($retornoTurma['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoTurma['codigoHelper'],
                        'campo'  => 'CodTurma',
                        'msg'    => $retornoTurma['msg']
                    ];
                }

                if ($retornoHorario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoHorario['codigoHelper'],
                        'campo'  => 'CodHorario',
                        'msg'    => $retornoHorario['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setCodProfessor($resultado->codProfessor);
                    $this->setCodTurma($resultado->codTurma);
                    $this->setCodHorario($resultado->codHorario);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->consultar(
                        $this->getCodigo(),
                        $this->getCodProfessor(),
                        $this->getCodTurma(),
                        $this->getCodHorario()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
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

        // Monta retorno único
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

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    /**
     * Altera dados de um mapa existente
     */
    public function alterar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo"       => '0',
                "codProfessor" => '0',
                "codTurma"     => '0',
                "codHorario"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Pelo menos um dos três parâmetros precisam ter dados para acontecer a atualização
                if (trim($resultado->codProfessor) == '' && trim($resultado->codTurma) == '' && trim($resultado->codHorario) == '') {
                    $erros[] = [
                        'codigo' => 12,
                        'msg'    => 'Pelo menos um parâmetro precisa ser passado para atualização'
                    ];
                } else {
                    // Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoCodigo    = validarDados($resultado->codigo, 'int', true);
                    $retornoProfessor = validarDadosConsulta($resultado->codProfessor, 'int');
                    $retornoTurma     = validarDadosConsulta($resultado->codTurma, 'int');
                    $retornoHorario   = validarDadosConsulta($resultado->codHorario, 'int');

                    if ($retornoCodigo['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoCodigo['codigoHelper'],
                            'campo'  => 'Codigo',
                            'msg'    => $retornoCodigo['msg']
                        ];
                    }

                    if ($retornoProfessor['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoProfessor['codigoHelper'],
                            'campo'  => 'CodProfessor',
                            'msg'    => $retornoProfessor['msg']
                        ];
                    }

                    if ($retornoTurma['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoTurma['codigoHelper'],
                            'campo'  => 'CodTurma',
                            'msg'    => $retornoTurma['msg']
                        ];
                    }

                    if ($retornoHorario['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoHorario['codigoHelper'],
                            'campo'  => 'CodHorario',
                            'msg'    => $retornoHorario['msg']
                        ];
                    }

                    // Se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setCodProfessor($resultado->codProfessor);
                        $this->setCodTurma($resultado->codTurma);
                        $this->setCodHorario($resultado->codHorario);

                        $this->load->model('M_mapa');
                        $resBanco = $this->M_mapa->alterar(
                            $this->getCodigo(),
                            $this->getCodProfessor(),
                            $this->getCodTurma(),
                            $this->getCodHorario()
                        );

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            // Captura erro do banco
                            $erros[] = [
                                'codigo' => $resBanco['codigo'],
                                'msg'    => $resBanco['msg']
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    /**
     * Desativa um mapa (Exclusão lógica)
     */
    public function desativar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar código quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->desativar($this->getCodigo());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
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

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }
}