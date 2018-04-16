<div class="form-group field text">

    <label class="form__field-label">$Title</label>

    <div class="form__field-holder" style="padding: 7px 1.5385rem; position: relative;">
        <div class="google-search-preview">
            <h3><a href="$Page.Link" target="_blank"><% if $RenderedTitle %>$RenderedTitle<% else %>$Page.Title<% end_if %></a></h3>
            <div class="google-url-preview">$Page.AbsoluteLink</div>
            <div class="snippet"><% if $Page.MetaDescription %>$Page.MetaDescription.LimitCharacters(320).RAW<% else_if $FirstParagraph %>$FirstParagraph.LimitCharacters(320).RAW<% else %><em>No description found for this page</em><% end_if %></div>
        </div>
    </div>
</div>