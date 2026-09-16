<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(): Response
    {
        $employees = Employee::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'position' => $employee->position,
                'active' => $employee->active,
            ]);

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Employees/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],

            'identity_number' => [
                'nullable',
                'string',
                'max:30',
                'unique:employees,identity_number',
            ],

            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],

            'email' => [
                'nullable',
                'email',
                'max:150',
                'unique:employees,email',
            ],

            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],

            'address' => ['nullable', 'string'],
            'position' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'active' => ['boolean'],
        ]);

        $nextNumber = (Employee::withTrashed()->max('id') ?? 0) + 1;

        $employeeCode = 'EMP-' . str_pad(
            (string) $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );

        Employee::create([
            ...$validated,
            'employee_code' => $employeeCode,
            'active' => $validated['active'] ?? true,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Empleado registrado correctamente.');
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('Employees/Edit', [
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'second_last_name' => $employee->second_last_name,
                'identity_number' => $employee->identity_number,
                'gender' => $employee->gender,
                'birth_date' => $employee->birth_date?->format('Y-m-d'),
                'email' => $employee->email,
                'phone' => $employee->phone,
                'alternate_phone' => $employee->alternate_phone,
                'address' => $employee->address,
                'position' => $employee->position,
                'hire_date' => $employee->hire_date?->format('Y-m-d'),
                'notes' => $employee->notes,
                'active' => $employee->active,
            ],
        ]);
    }

    public function update(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],

            'identity_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('employees', 'identity_number')
                    ->ignore($employee->id),
            ],

            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],

            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('employees', 'email')
                    ->ignore($employee->id),
            ],

            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],

            'address' => ['nullable', 'string'],
            'position' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'active' => ['boolean'],
        ]);

        $employee->update([
            ...$validated,
            'active' => $validated['active'] ?? false,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }
}