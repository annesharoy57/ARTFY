
@extends('artfy.layout')

@section('content')
<section style="max-width:760px;margin:50px auto;padding:0 24px;color:#414c2c;">

    <a href="{{ route('my-profile') }}" style="color:#55613a;text-decoration:none;">
        ← Back to My Profile
    </a>

    <h1 style="font-family:'Playfair Display',serif;font-size:36px;margin:24px 0 8px;">
        Edit Your Profile
    </h1>

    <p style="color:#72795f;margin-bottom:28px;">
        Update your personal information and profile photo.
    </p>

    @if($errors->any())
        <div style="background:#f8e4df;color:#8b3020;padding:15px;border-radius:8px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('my-profile.update') }}" method="POST" enctype="multipart/form-data"
          style="background:#f8f6f2;border:1px solid #e6e3d8;border-radius:16px;padding:30px;">
        @csrf
        @method('PUT')

        <div style="margin-bottom:20px;">
            <label for="name" style="display:block;margin-bottom:8px;font-weight:600;">Full Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                   style="width:100%;box-sizing:border-box;padding:12px;border:1px solid #d7d5c9;border-radius:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label for="email" style="display:block;margin-bottom:8px;font-weight:600;">Email Address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                   style="width:100%;box-sizing:border-box;padding:12px;border:1px solid #d7d5c9;border-radius:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label for="phone" style="display:block;margin-bottom:8px;font-weight:600;">Phone Number</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                   style="width:100%;box-sizing:border-box;padding:12px;border:1px solid #d7d5c9;border-radius:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label for="address" style="display:block;margin-bottom:8px;font-weight:600;">Address</label>
            <textarea id="address" name="address" rows="3"
                      style="width:100%;box-sizing:border-box;padding:12px;border:1px solid #d7d5c9;border-radius:8px;">{{ old('address', $user->address) }}</textarea>
        </div>

        <div style="margin-bottom:25px;">
            <label for="profile_photo" style="display:block;margin-bottom:8px;font-weight:600;">Profile Photo</label>

            @if($user->profile_photo)
                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Current profile photo"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;display:block;margin-bottom:12px;">
            @endif

            <input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp">
            <p style="font-size:12px;color:#72795f;margin-top:8px;">
                Optional. JPG, JPEG, PNG, or WEBP; maximum 2 MB.
            </p>
        </div>

        <button type="submit"
                style="padding:13px 24px;background:#55613a;color:white;border:0;border-radius:8px;cursor:pointer;">
            Save Changes
        </button>

        <a href="{{ route('my-profile') }}"
           style="display:inline-block;margin-left:12px;color:#55613a;text-decoration:none;">
            Cancel
        </a>
    </form>
</section>
@endsection
