<?php

namespace App\Services\WJC;

use App\Exceptions\BusinessException;
use App\Models\WJC\DrugLibrary;
use App\Models\WJC\DrugConflict;
use Illuminate\Pagination\LengthAwarePaginator;

class DrugLibraryService
{
    public function search(string $keyword, int $page, int $size): LengthAwarePaginator
    {
        return DrugLibrary::where('drug_name', 'like', "%{$keyword}%")
            ->select([
                'id', 'drug_name', 'specification', 'manufacturer',
                'usage', 'taboo', 'side_effect',
            ])
            ->orderBy('id', 'asc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($drug) {
                return [
                    'lib_id'        => $drug->id,
                    'drug_name'     => $drug->drug_name,
                    'specification' => $drug->specification,
                    'manufacturer'  => $drug->manufacturer,
                    'usage'         => $drug->usage,
                    'taboo'         => $drug->taboo,
                    'side_effect'   => $drug->side_effect,
                ];
            });
    }

    public function detail(int $libId): array
    {
        $drug = DrugLibrary::find($libId);

        if (!$drug) {
            throw new BusinessException('药品知识库记录不存在');
        }

        return [
            'lib_id'          => $drug->id,
            'drug_name'       => $drug->drug_name,
            'specification'   => $drug->specification,
            'manufacturer'    => $drug->manufacturer,
            'effect'          => $drug->effect,
            'taboo'           => $drug->taboo,
            'side_effect'     => $drug->side_effect,
            'match_conflict'  => $drug->match_conflict,
            'usage'           => $drug->usage,
            'save_note'       => $drug->save_note,
        ];
    }
}
