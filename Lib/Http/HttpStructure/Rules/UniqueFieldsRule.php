<?php
namespace App\Lib\Http\HttpStructure\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueFieldsRule implements ValidationRule
{
    private string|null $id;
    private array $fields;
    private string $table ;
    private string $messege ;

    public function __construct(string|null $id , string $table , array $fields , string $messege='')
    {
        $this->table = $table;
        $this->fields = $fields;
        $this->id = $id;
        $this->messege = $messege;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if($value){
            $query = DB::table($this->table);
            foreach ($this->fields as $field) {
                if($this->id)$query = $query->where('id','!=',$this->id);
                $query = $query->where($field, request($field));
            }
            if ($query->exists()) {
                if($this->messege) $fail($this->messege);
                else $fail("The combination of fields must be unique in {$this->table}.");
            }
        }
    }
}
