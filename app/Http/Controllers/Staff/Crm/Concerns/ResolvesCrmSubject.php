<?php

namespace App\Http\Controllers\Staff\Crm\Concerns;

use App\Models\CrmContact;
use App\Models\CrmOrganisation;
use Illuminate\Database\Eloquent\Model;

trait ResolvesCrmSubject
{
    protected function resolveCrmSubject(string $type, string $id): Model
    {
        $modelClass = match ($type) {
            'organisation' => CrmOrganisation::class,
            'contact' => CrmContact::class,
            default => abort(404),
        };

        return $modelClass::query()->findOrFail($id);
    }

    protected function crmSubjectRedirect(Model $subject): string
    {
        return $subject instanceof CrmContact
            ? route('staff.crm.contacts.show', $subject)
            : route('staff.crm.organisations.show', $subject);
    }
}
