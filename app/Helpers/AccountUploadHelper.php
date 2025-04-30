<?php
namespace App\Helpers;

use App\Models\Account;
use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;

use App\Contracts\RemittanceParserInterface;
use App\Parsers\PuregoldRemittanceParser;

class AccountUploadHelper
{
    public static function remittanceUploadData($accountId, $data)
    {
        $account = Account::find($accountId);
        if (!$account) return [];

        $uploadTemplate = UploadTemplate::where('name', 'REMITTANCE')->first();
        if (!$uploadTemplate) return [];

        $accountTemplate = AccountUploadTemplate::where('account_id', $accountId)
            ->where('upload_template_id', $uploadTemplate->id)
            ->first();
        if (!$accountTemplate) return [];

        $templateFieldsMap = self::mapAccountTemplateFields($accountTemplate);

        $parser = self::resolveParser($account->account_code, $uploadTemplate, $accountTemplate, $templateFieldsMap);

        if (!$parser) return [];

        return $parser->parse($data);
    }

    private static function mapAccountTemplateFields($accountTemplate)
    {
        return $accountTemplate->account_template_fields->mapWithKeys(function ($field) {
            return [
                $field->upload_template_field_id => [
                    'number' => $field->number,
                    'column_name' => $field->column_name,
                    'column_number' => $field->column_number,
                ],
            ];
        });
    }

    private static function resolveParser($accountCode, $uploadTemplate, $accountTemplate, $templateFieldsMap): ?RemittanceParserInterface
    {
        return match ($accountCode) {
            '1200081' => new PuregoldRemittanceParser($uploadTemplate, $accountTemplate, $templateFieldsMap),
            
            default => null,
        };
    }
}