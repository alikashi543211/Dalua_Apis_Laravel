{{-- Search Filters --}}
<div class="col-12">
    <hr>
    <form action="" class="filter_form">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <select name="enabled" id="enabled" class="form-control filter_input">
                        <option value="">Select Uploaded</option>
                        <option value="1" @if(request('enabled') == "1") selected @endif>Yes</option>
                        <option value="0" @if(request('enabled') == "0") selected @endif>No</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <select name="mode" id="mode" class="form-control filter_input">
                        <option value="">Select Type</option>
                        <option value="1" @if(request('mode') == SCHEDULE_EASY) selected @endif>Easy</option>
                        <option value="2" @if(request('mode') == SCHEDULE_ADVANCED) selected @endif>Advanced</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <select name="water_type" id="water_type" class="form-control filter_input">
                        <option value="">Select Water Type</option>
                        <option value="{{ WATER_MARINE }}" @if(request('water_type') == WATER_MARINE) selected @endif>{{ WATER_MARINE }}</option>
                        <option value="{{ WATER_FRESH }}" @if(request('water_type') == WATER_FRESH) selected @endif>{{ WATER_FRESH }}</option>
                    </select>
                </div>
            </div>
            @if(Route::currentRouteName() !== 'admin.schedules.public_requests')
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="public" id="access" class="form-control filter_input">
                            <option value="">Select Accessibility</option>
                            <option value="1" @if(request('public') == "1") selected @endif>Public</option>
                            <option value="0" @if(request('public') == "0") selected @endif>Private</option>
                        </select>
                    </div>
                </div>
            @endif
            <div class="col-md-2">
                <div class="form-group">
                    <select name="geo_location_id" id="access" class="form-control filter_input">
                        <option value="">Select Geo Location</option>
                        <option value="1" @if(request('geo_location_id') == "1") selected @endif>Enabled</option>
                        <option value="0" @if(request('geo_location_id') == "0") selected @endif>Disabled</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger clear_filter_button">Clear</button>
            </div>
        </div>
    </form>
</div>
