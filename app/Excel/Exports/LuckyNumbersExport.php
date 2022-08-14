<?php 

namespace App\Excel\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;


class LuckyNumbersExport extends DefaultValueBinder implements FromCollection,WithCustomValueBinder,WithStrictNullComparison,WithHeadings
{

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function headings(): array {
        return ["ID", "Número da Sorte", "Nome", "CPF", "Sorteio Final", "Data de Criação"];
    }

    public function collection()
    {
        return collect(DB::select("SELECT lucky_numbers.id, number as numero_da_sorte, name, cpf, lucky_numbers.final AS final, lucky_numbers.created_at FROM `lucky_numbers` LEFT JOIN users ON users.id = lucky_numbers.user_id"));
    }
}