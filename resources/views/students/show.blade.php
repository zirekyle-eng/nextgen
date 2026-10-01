@extends('layouts.master')

@section('content')
<style>
    .cand-show-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .cand-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cand-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .cand-hero p {
        margin: 5px 0 0;
        font-size: .83rem;
        opacity: .93;
    }

    .cand-hero-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .cand-hero-btn {
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .78rem;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
        background: rgba(255,255,255,.16);
        display: inline-flex;
        gap: 5px;
        align-items: center;
    }

    .cand-hero-btn:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .cand-grid {
        display: grid;
        grid-template-columns: 1.7fr 1fr;
        gap: 12px;
    }

    .cand-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
        margin-bottom: 12px;
    }

    .cand-card-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 10px 12px;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .cand-card-body {
        padding: 12px;
    }

    .cand-kv {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .cand-kv-item {
        border: 1px solid #e3eaf7;
        border-radius: 9px;
        background: #fbfdff;
        padding: 8px 10px;
    }

    .cand-kv-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #667ea4;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .cand-kv-value {
        font-size: .82rem;
        color: #1f3a64;
        font-weight: 700;
        line-height: 1.35;
    }

    .cand-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .68rem;
        font-weight: 800;
    }

    .st-pending { background: #fff7e8; color: #8d5e17; border: 1px solid #ffe2b5; }
    .st-approved { background: #eaf7ee; color: #1f6f38; border: 1px solid #c6e8d1; }
    .st-rejected { background: #ffecef; color: #a62132; border: 1px solid #ffcdd5; }
    .st-default { background: #eef2f8; color: #4d638a; border: 1px solid #d4deec; }

    .cand-action-col .btn {
        border-radius: 8px !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
    }

    @media (max-width: 1024px) {
        .cand-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .cand-kv {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="cand-show-page">
    <section class="cand-hero">
        <div>
            <h2>{{ $student->first_name }} {{ $student->last_name }}</h2>
            <p>Candidate profile details and approval actions.</p>
        </div>
        <div class="cand-hero-actions">
            <a href="{{ route('candidates.edit', $student) }}" class="cand-hero-btn">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('candidates.index') }}" class="cand-hero-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </section>

    @include('includes.alerts')

    <section class="cand-grid">
        <div>
            <article class="cand-card">
                <div class="cand-card-head">Personal Information</div>
                <div class="cand-card-body">
                    <div class="cand-kv">
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">First Name</div>
                            <div class="cand-kv-value">{{ $student->first_name }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Last Name</div>
                            <div class="cand-kv-value">{{ $student->last_name }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Date of Birth</div>
                            <div class="cand-kv-value">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Age</div>
                            <div class="cand-kv-value">{{ $student->dob ? $student->age . ' years' : 'N/A' }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Country</div>
                            <div class="cand-kv-value">{{ $student->country ?: 'N/A' }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Religion</div>
                            <div class="cand-kv-value">{{ $student->religion_status ?: 'Not specified' }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Status</div>
                            <div class="cand-kv-value">
                                @if($student->status === 'pending')
                                    <span class="cand-status st-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                                @elseif($student->status === 'approved')
                                    <span class="cand-status st-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                @elseif($student->status === 'rejected')
                                    <span class="cand-status st-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                @else
                                    <span class="cand-status st-default">{{ $student->status ?: 'Unknown' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="cand-card">
                <div class="cand-card-head">Academic Information</div>
                <div class="cand-card-body">
                    <div class="cand-kv">
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Academic Stage</div>
                            <div class="cand-kv-value">{{ $student->stage ?: 'Not specified' }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Preferred Start Date</div>
                            <div class="cand-kv-value">{{ $student->preferred_start_date ?: 'Not specified' }}</div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="cand-card">
                <div class="cand-card-head">Guardian Information</div>
                <div class="cand-card-body">
                    <div class="cand-kv">
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Guardian Name</div>
                            <div class="cand-kv-value">
                                <a href="{{ route('guardians.show', $student->guardian) }}">
                                    {{ $student->guardian->first_name }} {{ $student->guardian->last_name }}
                                </a>
                            </div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Relationship</div>
                            <div class="cand-kv-value">{{ $student->guardian->role ? ucfirst($student->guardian->role) : 'N/A' }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Email</div>
                            <div class="cand-kv-value"><a href="mailto:{{ $student->guardian->email }}">{{ $student->guardian->email }}</a></div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Phone</div>
                            <div class="cand-kv-value">{{ $student->guardian->phone ? ($student->guardian->phone_prefix . $student->guardian->phone) : 'Not provided' }}</div>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <div class="cand-action-col">
            <article class="cand-card">
                <div class="cand-card-head">Record Information</div>
                <div class="cand-card-body">
                    <div class="cand-kv" style="grid-template-columns: 1fr;">
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Created At</div>
                            <div class="cand-kv-value">{{ $student->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="cand-kv-item">
                            <div class="cand-kv-label">Last Updated</div>
                            <div class="cand-kv-value">{{ $student->updated_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="cand-card">
                <div class="cand-card-head">Actions</div>
                <div class="cand-card-body">
                    <div class="d-grid gap-2">
                        @if(strtolower((string)$student->status) !== 'approved')
                            <button type="button" class="btn btn-success w-100" data-toggle="modal" data-target="#approveModal">
                                <i class="fas fa-check-circle mr-1"></i> Approve Student
                            </button>
                        @endif

                        <a href="{{ route('candidates.edit', $student) }}" class="btn btn-primary">
                            <i class="fas fa-edit mr-1"></i> Edit Candidate
                        </a>

                        <a href="{{ route('guardians.show', $student->guardian) }}" class="btn btn-info">
                            <i class="fas fa-user mr-1"></i> View Guardian
                        </a>

                        <form action="{{ route('candidates.destroy', $student) }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash mr-1"></i> Delete Candidate
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(120deg, #0f2f66 0%, #c32033 100%);">
                <h5 class="modal-title text-white" style="font-weight: 700;">
                    <i class="fas fa-check-circle mr-2"></i> Approve Candidate
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('candidates.approveWithClass', $student) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="my_class_id" class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="my_class_id" id="my_class_id" class="form-control @error('my_class_id') is-invalid @enderror" required onchange="loadSections(this.value)">
                            <option value="">-- Choose a Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('my_class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="section_id" class="form-label fw-bold">Select Section <span class="text-danger">*</span></label>
                        <select name="section_id" id="section_id" class="form-control @error('section_id') is-invalid @enderror" required>
                            <option value="">-- First Select a Class --</option>
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info mb-0">
                        Approving this candidate adds them to the selected class and section in the main system.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve & Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadSections(classId) {
    const sectionSelect = document.getElementById('section_id');

    if (!classId) {
        sectionSelect.innerHTML = '<option value="">-- First Select a Class --</option>';
        return;
    }

    fetch(`/api/classes/${classId}/sections`)
        .then(response => response.json())
        .then(data => {
            let html = '<option value="">-- Choose a Section --</option>';
            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(section => {
                    html += `<option value="${section.id}">${section.name}</option>`;
                });
            } else {
                html = '<option value="">No sections available</option>';
            }
            sectionSelect.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading sections:', error);
            sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
        });
}
</script>
@endsection
