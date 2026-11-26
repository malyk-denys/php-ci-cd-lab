@php($currentDiscount = $discount ?? null)

<x-form.errors />

<div class="row g-3">
    <div class="col-md-4"><x-form.input name="code" label="Промокод" :value="$currentDiscount?->code" placeholder="STUDENT20" required /></div>
    <div class="col-md-8"><x-form.input name="name" label="Назва знижки" :value="$currentDiscount?->name" required /></div>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label for="type" class="form-label">Тип <span class="required-mark">*</span></label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="percentage" @selected(old('type', $currentDiscount?->type ?? 'percentage') === 'percentage')>Відсоткова</option>
            <option value="fixed" @selected(old('type', $currentDiscount?->type) === 'fixed')>Фіксована сума</option>
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4"><x-form.input name="value" label="Значення" type="number" step="0.01" min="0.01" :value="$currentDiscount?->value" required /></div>
    <div class="col-md-4 d-flex align-items-center">
        <div class="form-check form-switch mt-3">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked((bool) old('is_active', $currentDiscount?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Знижка активна</label>
        </div>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-6"><x-form.input name="active_from" label="Діє від" type="datetime-local" :value="$currentDiscount?->active_from?->format('Y-m-d\TH:i')" /></div>
    <div class="col-md-6"><x-form.input name="active_until" label="Діє до" type="datetime-local" :value="$currentDiscount?->active_until?->format('Y-m-d\TH:i')" /></div>
</div>

<div class="d-flex gap-2 pt-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn btn-outline-secondary" href="{{ route('discounts.index') }}">Скасувати</a>
</div>
