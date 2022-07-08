<% if $Title && $ShowTitle %><h2 class="element__title">$Title</h2><% end_if %>
<% if $Content %><div class="element__content">$Content</div><% end_if %>

<% if $PostsList %>
    <div class="row">
        <% loop $PostsList %>
            <div class="col-md-4 card">
                <img src="$FeaturedImage.URL" class="card-img-top">
                <div class="card-body">
                    <h3 class="card-title">
                        <a href="$Link.ATT" title="Go to the $Title post">
                            $Title
                        </a>
                    </h3>
                    <% include SilverStripe\\Blog\\EntryMeta %>
                    <div class="card-text">
                        $Summary
                    </div>
                    <a href="$Link.ATT" class="btn btn-primary">Read more</a>
                </div>
            </div>
        <% end_loop %>
        <p><a href="$Blog.Link" class="btn btn-primary" title="Go to the $Title page">View all posts</a></p>
    </div>

<% else %>
    <p>No recent posts.</p>
<% end_if %>
