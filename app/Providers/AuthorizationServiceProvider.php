<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Scopes\AssignedPatientsScope;
use App\Scopes\OwnAppointmentsScope;
use App\Models\Chair;
use App\Models\ClinicalEvolutionNote;
use App\Models\DentistCommission;
use App\Models\DentistSchedule;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Odontogram;
use App\Models\OdontogramAttachment;
use App\Models\OdontogramToothState;
use App\Models\PatientDocument;
use App\Models\PatientEmergencyContact;
use App\Models\PatientMedicalHistory;
use App\Models\Payment;
use App\Models\PeriodontalChart;
use App\Models\PeriodontalMeasurement;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\ScheduleException;
use App\Models\Supplier;
use App\Models\Supply;
use App\Models\SupplyBatch;
use App\Models\SupplyCategory;
use App\Models\SupplyMovement;
use App\Models\TenantUser;
use App\Models\ToothTreatmentEvent;
use App\Models\TreatmentCatalog;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanInstallment;
use App\Models\TreatmentPlanItem;
use App\Policies\PatientsPolicy;
use App\Policies\OdontogramPolicy;
use App\Policies\AppointmentsPolicy;
use App\Policies\TreatmentsPolicy;
use App\Policies\InvoicesPolicy;
use App\Policies\ReportsPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\StaffPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Autorización por rol: conecta cada modelo con la política de su módulo y
 * aplica los recortes de visibilidad (citas propias / pacientes asignados).
 */
class AuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Patient::class, PatientsPolicy::class);
        Gate::policy(PatientDocument::class, PatientsPolicy::class);
        Gate::policy(PatientEmergencyContact::class, PatientsPolicy::class);
        Gate::policy(PatientMedicalHistory::class, PatientsPolicy::class);
        Gate::policy(Odontogram::class, OdontogramPolicy::class);
        Gate::policy(OdontogramToothState::class, OdontogramPolicy::class);
        Gate::policy(OdontogramAttachment::class, OdontogramPolicy::class);
        Gate::policy(ToothTreatmentEvent::class, OdontogramPolicy::class);
        Gate::policy(PeriodontalChart::class, OdontogramPolicy::class);
        Gate::policy(PeriodontalMeasurement::class, OdontogramPolicy::class);
        Gate::policy(ClinicalEvolutionNote::class, OdontogramPolicy::class);
        Gate::policy(Prescription::class, OdontogramPolicy::class);
        Gate::policy(PrescriptionItem::class, OdontogramPolicy::class);
        Gate::policy(Appointment::class, AppointmentsPolicy::class);
        Gate::policy(Chair::class, AppointmentsPolicy::class);
        Gate::policy(DentistSchedule::class, AppointmentsPolicy::class);
        Gate::policy(ScheduleException::class, AppointmentsPolicy::class);
        Gate::policy(TreatmentCatalog::class, TreatmentsPolicy::class);
        Gate::policy(TreatmentPlan::class, TreatmentsPolicy::class);
        Gate::policy(TreatmentPlanItem::class, TreatmentsPolicy::class);
        Gate::policy(TreatmentPlanInstallment::class, TreatmentsPolicy::class);
        Gate::policy(Invoice::class, InvoicesPolicy::class);
        Gate::policy(InvoiceItem::class, InvoicesPolicy::class);
        Gate::policy(Payment::class, InvoicesPolicy::class);
        Gate::policy(DentistCommission::class, ReportsPolicy::class);
        Gate::policy(Supplier::class, InventoryPolicy::class);
        Gate::policy(SupplyCategory::class, InventoryPolicy::class);
        Gate::policy(Supply::class, InventoryPolicy::class);
        Gate::policy(SupplyBatch::class, InventoryPolicy::class);
        Gate::policy(SupplyMovement::class, InventoryPolicy::class);
        Gate::policy(TenantUser::class, StaffPolicy::class);
        Gate::policy(Role::class, StaffPolicy::class);

        Patient::addGlobalScope(new AssignedPatientsScope());
        Appointment::addGlobalScope(new OwnAppointmentsScope());
    }
}
