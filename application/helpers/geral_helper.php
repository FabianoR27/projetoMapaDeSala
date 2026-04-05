<?php
defined('BASEPATH') or exit('No direct script access allowed');

// função para verificar os parâmetros que estão vindo do Front-end
function verificarParam($atributos, $lista)
{
    // verificar se os elementos do Front-end estão nos atributos necessários
    foreach ($lista as $key => $value) {
        if (array_key_exists($key, get_object_vars($atributos))) {
            $status = 1;
        } else {
            $status = 0;
            break;
        }
    }

    // verificando a quantidade de elementos do Front-end
    if (count(get_object_vars($atributos)) != count($lista)) {
        $status = 0;
    }

    return $status;
}

// função para verificar os tipos de dados que estão vindo do Front-end
function validarDados($valor, $tipo, $tamanhoZero = true) {

    // verfica vazio ou nulo
    if (is_null($valor) || $valor === '') {
        return array('codigoHelper' => 2, 'msg' => 'Conteúdo nulo ou vazio.');
    }

    // se considerar zero como vazio
    if ($tamanhoZero && ($valor === 0 || $valor === '0')) {
        return array('codigoHelper' => 3, 'msg' => 'Conteúdo zerado.');
    }

    switch ($tipo) {
        case 'int':
            // filtra como inteiro, aceita 123 e '123'
            if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                return array('codigoHelper' => 4, 'msg' => 'Valor não é um inteiro válido.');
            }
            break;

        case 'string':
            // garante que é string não vazia após o trim
            if (!is_string($valor) || trim($valor) === '') {
                return array('codigoHelper' => 5, 'msg' => 'Valor não é um texto válido.');
            }
            break;

        case 'date':
            // verifica o padrão da data
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)) {
                return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.');
            } else {
                // tenta criar DataTime no formato Y-m-d
                $d = DateTime::createFromFormat('Y-m-d', $valor);
                if (($d->format('Y-m-d') !== $valor) == false) {
                    return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.');
                }
            }
            break;

        case 'hora':
            // verifica se tem padrão de hora
            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) {
                return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido.');
            }
            break;

        default:
            return array('codigoHelper' => 0, 'msg' => 'Tipo de dado não definido.');
    }

    // Valor default da variável $retorno, caso não ocorra erro
    return array('codigoHelper' => 1, 'msg' => 'Validação correta.');
}

/**
 * Função para verificar os tipos de dados para Consulta
 */
function validarDadosConsulta($valor, $tipo) {

    if ($valor != '') {
        switch ($tipo) {
            case 'int':
                // Filtra como inteiro, aceita '123' ou 123
                if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                    return array('codigoHelper' => 4, 'msg' => 'Conteúdo não inteiro.');
                }
                break;
            case 'string':
                // Garante que é string não vazia após trim
                if (!is_string($valor) || trim($valor) === '') {
                    return array('codigoHelper' => 5, 'msg' => 'Conteúdo não é um texto.');
                }
                break;
            case 'date':
                // Verifico se tem padrão de data
                if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)) {
                    return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.');
                } else {
                    // Tenta criar DateTime no formato Y-m-d
                    $d = DateTime::createFromFormat('Y-m-d', $valor);
                    if (($d && $d->format('Y-m-d') === $valor) == false) {
                        return array('codigoHelper' => 6, 'msg' => 'Data inválida.');
                    }
                }
                break;
            case 'hora':
                // Verifico se tem padrão de hora
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) {
                    return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido.');
                }
                break;
            default:
                return array('codigoHelper' => 97, 'msg' => 'Tipo de dado não definido.');
        }
    }
    // Valor default da variável $retorno caso não ocorra erro
    return array('codigoHelper' => 0, 'msg' => 'Validação correta.');
}
// Função para verificar se datas ou horários são maiores entre si
function compararDataHora($valorInicial, $valorFinal, $tipo) {
    // Convertemos as strings para timestamp (segundos)
    $inicio = strtotime($valorInicial);
    $fim = strtotime($valorFinal);

    // Verifica se as datas passadas são válidas
    if ($inicio === false || $fim === false) {
        return array('codigoHelper' => 98, 'msg' => 'Formato de data/hora inválido.');
    }

    // Só disparar o erro se o inicial for MAIOR que o final
    if ($inicio > $fim) {
        switch ($tipo) {
            case 'hora':
                return array('codigoHelper' => 13, 'msg' => 'Hora inicial é maior que a hora final.');
            case 'data':
                return array('codigoHelper' => 14, 'msg' => 'Data inicial é maior que a data final.');
            default:
                return array('codigoHelper' => 97, 'msg' => 'Tipo de comparação não definido.');
        }
    }

    // Se chegou aqui, a validação passou (Início é menor ou igual ao Fim)
    return array('codigoHelper' => 1, 'msg' => 'Validação correta.');
}
?>