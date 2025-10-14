<div class="emmanuel-saleem-social-auth">
    @if(session('error'))
        <div class="es-alert es-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="es-alert es-alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form class="es-required-form" method="POST">
        @csrf
        @php($fields = config('emmanuel-saleem-social-auth.required_fields', []))
        @foreach($fields as $field)
            <div class="es-field">
                <label class="es-label">{{ $field['label'] ?? $field['name'] }}</label>
                @if(($field['type'] ?? 'text') === 'select')
                    <select name="extra[{{ $field['name'] }}]" class="es-input" {{ !empty($field['required']) ? 'required' : '' }}>
                        @foreach(($field['options'] ?? []) as $optValue => $optLabel)
                            <option value="{{ $optValue }}" {{ ($field['default'] ?? null) == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="extra[{{ $field['name'] }}]" class="es-input" value="{{ $field['default'] ?? '' }}" {{ !empty($field['required']) ? 'required' : '' }} />
                @endif
            </div>
        @endforeach

        <button type="submit" formaction="{{ route('emmanuel-saleem.social-auth.login.google') }}" class="es-social-btn es-google-btn">
            <svg class="es-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            <span>{{ config('emmanuel-saleem-social-auth.labels.google_button', 'Continue with Google') }}</span>
        </button>

        <button type="submit" formaction="{{ route('emmanuel-saleem.social-auth.login.microsoft') }}" class="es-social-btn es-microsoft-btn">
            <svg class="es-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="#f25022" d="M11.4 11.4H2V2h9.4v9.4z"/>
                <path fill="#00a4ef" d="M22 11.4h-9.4V2H22v9.4z"/>
                <path fill="#7fba00" d="M11.4 22H2v-9.4h9.4V22z"/>
                <path fill="#ffb900" d="M22 22h-9.4v-9.4H22V22z"/>
            </svg>
            <span>{{ config('emmanuel-saleem-social-auth.labels.microsoft_button', 'Continue with Microsoft') }}</span>
        </button>
    </form>

    @if(config('emmanuel-saleem-social-auth.show_footer', true))
        <div class="es-footer">
            {{ config('emmanuel-saleem-social-auth.footer_text', 'Powered by Emmanuel Saleem Social Auth') }}
        </div>
    @endif
</div>

<style>
    .emmanuel-saleem-social-auth {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    .es-alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .es-alert-error {
        background-color: #fee;
        color: #c33;
        border: 1px solid #fcc;
    }

    .es-alert-success {
        background-color: #efe;
        color: #3c3;
        border: 1px solid #cfc;
    }

    .es-social-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .es-required-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 16px;
    }

    .es-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .es-label {
        font-size: 13px;
        color: #444;
    }

    .es-input {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    .es-social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 12px 24px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: white;
        color: #333;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .es-social-btn:hover {
        background: #f8f8f8;
        border-color: #ccc;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    .es-social-btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }

    .es-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .es-google-btn {
        border-color: #4285f4;
    }

    .es-google-btn:hover {
        background: #f8fbff;
        border-color: #4285f4;
    }

    .es-microsoft-btn {
        border-color: #00a4ef;
    }

    .es-microsoft-btn:hover {
        background: #f8fcff;
        border-color: #00a4ef;
    }

    .es-footer {
        margin-top: 24px;
        text-align: center;
        font-size: 12px;
        color: #666;
        padding: 8px;
    }

    @media (max-width: 480px) {
        .emmanuel-saleem-social-auth {
            max-width: 100%;
            padding: 0 16px;
        }

        .es-social-btn {
            font-size: 14px;
            padding: 10px 20px;
        }
    }
</style>