@php($currentEvent = $event ?? null)

<x-form.errors />

<div class="row g-3">
    <div class="col-md-7"><x-form.input name="name" label="Назва події" :value="$currentEvent?->name" required /></div>
    <div class="col-md-5"><x-form.input name="slug" label="Slug" :value="$currentEvent?->slug" placeholder="movie-premiere-2026" required /></div>
</div>
<div class="row g-3">
    <div class="col-md-5"><x-form.input name="venue" label="Зала / місце проведення" :value="$currentEvent?->venue" required /></div>
    <div class="col-md-4">
        <x-form.input name="starts_at" label="Дата й час" type="datetime-local" :value="$currentEvent?->starts_at?->format('Y-m-d\TH:i')" required />
    </div>
    <div class="col-md-3">
        <label for="status" class="form-label">Статус <span class="required-mark">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['scheduled' => 'Заплановано', 'cancelled' => 'Скасовано', 'completed' => 'Завершено'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $currentEvent?->status ?? 'scheduled') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<x-form.textarea name="description" label="Опис" :value="$currentEvent?->description" rows="5" />

<div class="d-flex gap-2 pt-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn btn-outline-secondary" href="{{ route('events.index') }}">Скасувати</a>
</div>
