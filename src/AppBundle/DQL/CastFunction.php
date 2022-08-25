<?php

namespace AppBundle\DQL;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Expr\Literal;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class CastFunction extends FunctionNode
{
    /**
     * @var array
     */
    public $parameters = array();
    public $parameterIsExpression = false;

    const PARAMETER_KEY = 'expression';
    const TYPE_KEY = 'type';


    /**
     * @var array
     */
    protected $supportedTypes = [
        'char',
        'string',
        'text',
        'date',
        'datetime',
        'time',
        'int',
        'integer',
        'decimal',
        'json',
        'bool',
        'boolean',
        'binary'
    ];

//    public function parse(Parser $parser)
//    {
//        $parser->match(Lexer::T_IDENTIFIER);
//        $parser->match(Lexer::T_OPEN_PARENTHESIS);
//        $this->expr1 = $parser->StringPrimary();
//        $parser->match(Lexer::T_AS);
//        dd($parser->);
//
//        $this->expr2 = $parser->StringPrimary();
//        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
//    }

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        try {
            $this->parameters[self::PARAMETER_KEY] = $parser->StringPrimary();
        } catch (\Exception $e) {
            $this->parameters[self::PARAMETER_KEY] = $parser->StringExpression();
            $this->parameterIsExpression = true;
        }


        $parser->match(Lexer::T_AS);

        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $type = $lexer->token['value'];

        if ($lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $parser->match(Lexer::T_OPEN_PARENTHESIS);
            $parameter = $parser->Literal();
            $parameters = [
                $parameter->value
            ];
            if ($lexer->isNextToken(Lexer::T_COMMA)) {
                while ($lexer->isNextToken(Lexer::T_COMMA)) {
                    $parser->match(Lexer::T_COMMA);
                    $parameter = $parser->Literal();
                    $parameters[] = $parameter->value;
                }
            }
            $parser->match(Lexer::T_CLOSE_PARENTHESIS);
            $type .= '(' . implode(', ', $parameters) . ')';
        }

        if (!$this->checkType($type)) {
            $parser->syntaxError(
                sprintf(
                    'Type unsupported. Supported types are: "%s"',
                    implode(', ', $this->supportedTypes)
                ),
                $lexer->token
            );
        }

        $this->parameters[self::TYPE_KEY] = $type;

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        $expression = $this->parameters[self::PARAMETER_KEY]->dispatch($sqlWalker);
        $type  = $this->parameters[self::TYPE_KEY];
        $type = strtolower($type);
        if($this->parameterIsExpression) {
            $expression = '(' . $expression . ')';
        }
        $isBoolean = $type === 'bool' || $type === 'boolean';
        if ($type === 'char') {
            $type = 'char(1)';
        } elseif ($type === 'string' || $type === 'text' || $type === 'json') {
            $type = 'char';
        } elseif ($type === 'int' || $type === 'integer') {
            $type = 'signed';
        } elseif ($isBoolean) {
            return 'IF(CAST(' . $expression . ' as signed) > 0, true, false)';
        }
        return 'CAST(' . $expression . ' AS ' . $type . ')';
    }

    /**
     * Check that given type is supported.
     *
     * @param string $type
     * @return bool
     */
    protected function checkType($type)
    {
        $type = strtolower(trim($type));
        foreach ($this->supportedTypes as $supportedType) {
            if (strpos($type, $supportedType) === 0) {
                return true;
            }
        }

        return false;
    }
}
