<?php

namespace App\Lib\Http\HttpStructure\Enums;

enum ParameterOperator:string {
    case equals=' = ';
    case notEqual = ' != ';
    case biggerThan = ' > ';
    case biggerThanOrEquals = ' >= ';
    case lowerThan = '<';
    case lowerThanOrEquals = ' <= ';
}
