@extends('admin.layout')

@section('title', 'FAQ review')

@section('content')
<div class="admin-page-head">
    <div>
        <div class="eyebrow">Website content</div>
        <h1>Frequently asked questions</h1>
        <p class="muted">Review every answer before it becomes visible to families and visitors.</p>
    </div>
    @can('create', App\Models\Faq::class)
        <button class="btn btn-primary btn-sm" type="button" data-toggle-details="new-faq">Add question</button>
    @endcan
</div>

@if(session('status'))
    <div class="admin-alert" role="status">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="admin-alert admin-alert--error" role="alert">
        <b>Review the highlighted information.</b>
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

@can('create', App\Models\Faq::class)
    <details class="admin-panel" id="new-faq">
        <summary class="panel-head"><h2>Add an FAQ</h2></summary>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.faqs.store') }}" class="admin-fields">
                @csrf
                <div class="admin-field"><label for="new-faq-question">Question</label><input id="new-faq-question" name="question" maxlength="500" required></div>
                <div class="admin-field"><label for="new-faq-answer">Answer</label><textarea id="new-faq-answer" name="answer" maxlength="10000" required></textarea></div>
                <div class="form-grid">
                    <div class="admin-field"><label for="new-faq-category">Category</label><select id="new-faq-category" name="faq_category_id"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                    <div class="admin-field"><label for="new-faq-order">Display order</label><input id="new-faq-order" name="sort_order" type="number" min="0" max="10000" value="{{ old('sort_order', 0) }}" required></div>
                </div>
                <button class="btn btn-primary btn-sm" type="submit">Save for review</button>
            </form>
        </div>
    </details>
@endcan

<div class="admin-detail-stack" style="margin-top:20px">
    @forelse($faqs as $faq)
        <section class="admin-panel">
            <div class="panel-head">
                <div>
                    <h2>{{ $faq->question }}</h2>
                    <div class="small muted">{{ $faq->category?->name ?? 'General' }} &middot; order {{ $faq->sort_order }}</div>
                </div>
                <span class="status {{ $faq->verified_at ? 'status-published' : 'status-review' }}">{{ $faq->verified_at ? 'Verified' : 'Review required' }}</span>
            </div>
            <div class="panel-body admin-form-grid">
                <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="admin-fields">
                    @csrf
                    @method('PATCH')
                    <div class="admin-field"><label for="faq-question-{{ $faq->id }}">Question</label><input id="faq-question-{{ $faq->id }}" name="question" value="{{ $faq->question }}" maxlength="500" required></div>
                    <div class="admin-field"><label for="faq-answer-{{ $faq->id }}">Answer</label><textarea id="faq-answer-{{ $faq->id }}" name="answer" maxlength="10000" required>{{ $faq->answer }}</textarea></div>
                    <div class="form-grid">
                        <div class="admin-field"><label for="faq-category-{{ $faq->id }}">Category</label><select id="faq-category-{{ $faq->id }}" name="faq_category_id"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($faq->faq_category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                        <div class="admin-field"><label for="faq-order-{{ $faq->id }}">Display order</label><input id="faq-order-{{ $faq->id }}" name="sort_order" type="number" min="0" max="10000" value="{{ $faq->sort_order }}" required></div>
                    </div>
                    <button class="btn btn-outline btn-sm" type="submit">Save answer</button>
                </form>
                @can('verify', $faq)
                    <form method="POST" action="{{ route('admin.faqs.verify', $faq) }}" class="admin-fields">
                        @csrf
                        @method('PATCH')
                        <div class="admin-field"><label for="faq-notes-{{ $faq->id }}">Verification note</label><textarea id="faq-notes-{{ $faq->id }}" name="verification_notes" maxlength="2000" style="min-height:110px">{{ $faq->verification_notes }}</textarea></div>
                        @if($faq->verified_at)
                            <p class="small muted">Verified by {{ $faq->verifier?->name ?? 'authorised staff' }} on {{ $faq->verified_at->format('d M Y H:i') }}.</p>
                            <button class="btn btn-outline btn-sm" type="submit" name="action" value="withdraw">Withdraw for review</button>
                        @else
                            <button class="btn btn-primary btn-sm" type="submit" name="action" value="verify">Verify and publish</button>
                        @endif
                    </form>
                @endcan
            </div>
        </section>
    @empty
        <div class="empty-state">No questions have been added.</div>
    @endforelse
</div>

{{ $faqs->links() }}
@endsection
