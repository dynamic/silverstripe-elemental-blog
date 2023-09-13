<div class="col-lg-4 col-md-6 col-sm-12 mb-3 element__promos__item">
    <div class="card h-100">
        <% if $FeaturedImage %>
            <a href="$Link" title="Read $MenuTitle.ATT">
                <img src="$FeaturedImage.FocusFill(500,330).URL" class="card-img-top" alt="$Image.Title.ATT">
            </a>
        <% end_if %>
        <div class="card-body">
            <h3 class="card-title">$Title</a></h3>
            <% include SilverStripe\\Blog\\EntryMeta %>
            <% if $Summary %><div class="card-text">$Summary</div><% end_if %>
            <p><a href="$Link.ATT" class="btn btn-outline-primary">Read more</a></p>
        </div>
    </div>
</div>
