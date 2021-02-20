<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Afranio Martins <afranioce@gmail.com> (https://gist.github.com/afranioce/81bff0bdb479a2c6a90e)
 *
 * @api
 */
class CpfCnpj extends Constraint
{
    public $cpf = false;
    public $cnpj = false;
    public $mask = false;
    public $messageMask = 'O {{ type }} não está em um formato válido.';
    public $message = 'O {{ type }} informado é inválido.';
}