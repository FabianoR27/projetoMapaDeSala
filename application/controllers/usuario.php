<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {
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
    private $usuario;
    private $senha;
    private $tipoUsuario;
    private $estatus;

    // --- Getters dos atributos ---

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function getTipoUsuario()
    {
        return $this->tipoUsuario;
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

    public function setUsuario($usuarioFront)
    {
        $this->usuario = $usuarioFront;
    }

    public function setSenha($senhaFront)
    {
        $this->senha = $senhaFront;
    }

    public function setTipoUsuario($tipoUsuarioFront)
    {
        $this->tipoUsuario = $tipoUsuarioFront;
    }

    public function setEstatus($estatusFront)
    {
        $this->estatus = $estatusFront;
    }

    // --- Métodos de Regra de Negócio ---

    /**
     * Insere um novo usuário
     */
    public function inserir() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "usuario"     => '0',
                "senha"       => '0',
                "tipoUsuario" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoUsuario     = validarDados($resultado->usuario, 'string', true);
                $retornoSenha       = validarDados($resultado->senha, 'string', true);
                $retornoTipoUsuario = validarDados($resultado->tipoUsuario, 'int', true);

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoUsuario['codigoHelper'],
                        'campo'  => 'Usuario',
                        'msg'    => $retornoUsuario['msg']
                    ];
                }

                if ($retornoSenha['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoSenha['codigoHelper'],
                        'campo'  => 'Senha',
                        'msg'    => $retornoSenha['msg']
                    ];
                }

                if ($retornoTipoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoTipoUsuario['codigoHelper'],
                        'campo'  => 'TipoUsuario',
                        'msg'    => $retornoTipoUsuario['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);
                    $this->setTipoUsuario($resultado->tipoUsuario);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->inserir(
                        $this->getUsuario(),
                        $this->getSenha(),
                        $this->getTipoUsuario()
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
     * Consulta usuários
     */
    public function consultar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo"      => '0',
                "usuario"     => '0',
                "tipoUsuario" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo      = validarDadosConsulta($resultado->codigo, 'int');
                $retornoUsuario     = validarDadosConsulta($resultado->usuario, 'string');
                $retornoTipoUsuario = validarDadosConsulta($resultado->tipoUsuario, 'int');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];
                }

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoUsuario['codigoHelper'],
                        'campo'  => 'Usuario',
                        'msg'    => $retornoUsuario['msg']
                    ];
                }

                if ($retornoTipoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoTipoUsuario['codigoHelper'],
                        'campo'  => 'TipoUsuario',
                        'msg'    => $retornoTipoUsuario['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setUsuario($resultado->usuario);
                    $this->setTipoUsuario($resultado->tipoUsuario);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->consultar(
                        $this->getCodigo(),
                        $this->getUsuario(),
                        $this->getTipoUsuario()
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
     * Altera dados de um usuário existente
     */
    public function alterar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo"      => '0',
                "usuario"     => '0',
                "senha"       => '0',
                "tipoUsuario" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Pelo menos um dos três parâmetros precisam ter dados para acontecer a atualização
                if (trim($resultado->usuario) == '' && trim($resultado->senha) == '' && trim($resultado->tipoUsuario) == '') {
                    $erros[] = [
                        'codigo' => 12,
                        'msg'    => 'Pelo menos um parâmetro precisa ser passado para atualização'
                    ];
                } else {
                    // Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoCodigo      = validarDados($resultado->codigo, 'int', true);
                    $retornoUsuario     = validarDadosConsulta($resultado->usuario, 'string');
                    $retornoSenha       = validarDadosConsulta($resultado->senha, 'string');
                    $retornoTipoUsuario = validarDadosConsulta($resultado->tipoUsuario, 'int');

                    if ($retornoCodigo['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoCodigo['codigoHelper'],
                            'campo'  => 'Codigo',
                            'msg'    => $retornoCodigo['msg']
                        ];
                    }

                    if ($retornoUsuario['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoUsuario['codigoHelper'],
                            'campo'  => 'Usuario',
                            'msg'    => $retornoUsuario['msg']
                        ];
                    }

                    if ($retornoSenha['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoSenha['codigoHelper'],
                            'campo'  => 'Senha',
                            'msg'    => $retornoSenha['msg']
                        ];
                    }

                    if ($retornoTipoUsuario['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoTipoUsuario['codigoHelper'],
                            'campo'  => 'TipoUsuario',
                            'msg'    => $retornoTipoUsuario['msg']
                        ];
                    }

                    // Se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setUsuario($resultado->usuario);
                        $this->setSenha($resultado->senha);
                        $this->setTipoUsuario($resultado->tipoUsuario);

                        $this->load->model('M_usuario');
                        $resBanco = $this->M_usuario->alterar(
                            $this->getCodigo(),
                            $this->getUsuario(),
                            $this->getSenha(),
                            $this->getTipoUsuario()
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
     * Desativa um usuário (Exclusão lógica)
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

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->desativar($this->getCodigo());

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