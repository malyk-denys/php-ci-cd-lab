@php
    $currentTicket = $ticket ?? null;
    $selectedDiscounts = collect(old('discounts', $currentTicket?->discounts->modelKeys() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<x-form.errors />

<div class="row g-3">
    <div class="col-md-6">
        <label for="event_id" class="form-label">Подія <span class="required-mark">*</span></label>
        <select name="event_id" id="event_id" class="form-select @error('event_id') is-invalid @enderror" required>
            <option value="">Оберіть подію</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected((int) old('event_id', $currentTicket?->event_id ?? request('event_id')) === $event->id)>
                    {{ $event->name }} · {{ $event->starts_at->format('d.m.Y H:i') }}
                </option>
            @endforeach
        </select>
        @error('event_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <x-form.input name="code" label="Код квитка" :value="$currentTicket?->code" placeholder="TKT-2026-001" required />
    </div>
    <div class="col-md-3">
        <x-form.input name="seat_number" label="Місце" :value="$currentTicket?->seat_number" placeholder="A-12" required />
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <x-form.input name="price" label="Ціна, грн" type="number" step="0.01" min="0.01" :value="$currentTicket?->price" required />
    </div>
    <div class="col-md-5">
        <x-form.input name="customer_email" label="Email покупця" type="email" :value="$currentTicket?->customer_email" placeholder="buyer@example.com" required />
    </div>
    <div class="col-md-3">
        <label for="status" class="form-label">Статус <span class="required-mark">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (['available' => 'Доступний', 'reserved' => 'Зарезервований', 'sold' => 'Проданий', 'cancelled' => 'Скасований'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $currentTicket?->status ?? 'available') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <x-form.input
            name="sold_at"
            label="Дата продажу"
            type="datetime-local"
            :value="$currentTicket?->sold_at?->format('Y-m-d\TH:i')"
            help="Залиште порожнім для доступного або зарезервованого квитка."
        />
    </div>
    <div class="col-md-7">
        <fieldset class="mb-3">
            <legend class="form-label mb-2">Знижки M:M</legend>
            <div class="border rounded-3 p-3 bg-light">
                @forelse ($discounts as $discount)
                    <div class="form-check form-check-inline mb-2">
                        <input
                            class="form-check-input @error('discounts') is-invalid @enderror"
                            type="checkbox"
                            name="discounts[]"
                            id="discount_{{ $discount->id }}"
                            value="{{ $discount->id }}"
                            @checked(in_array($discount->id, $selectedDiscounts, true))
                        >
                        <label class="form-check-label" for="discount_{{ $discount->id }}">
                            {{ $discount->code }} · {{ $discount->type === 'percentage' ? rtrim(rtrim(number_format($discount->value, 2, '.', ''), '0'), '.') . '%' : number_format($discount->value, 2, ',', ' ') . ' грн' }}
                        </label>
                    </div>
                @empty
                    <span class="text-secondary">Знижок ще немає.</span>
                @endforelse
            </div>
            @error('discounts')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('discounts.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </fieldset>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 pt-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Скасувати</a>
</div>
