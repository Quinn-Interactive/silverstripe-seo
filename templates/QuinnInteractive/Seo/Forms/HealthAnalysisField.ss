<div class="form-group field health-analysis-field">

    <label class="form__field-label">$Title</label>

    <div class="form__field-holder health-analyses">
        <% loop $Results %>
            <% if not $Hidden %>
                <div class="health-analysis">
                    <div class="health-indicator health-$Level"></div>
                    <div class="d-inline-block" style="max-width: 80%">$Response.RAW</div>
                </div>
            <% end_if %>
        <% end_loop %>
    </div>
</div>