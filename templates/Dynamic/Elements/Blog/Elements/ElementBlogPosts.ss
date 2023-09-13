<% if $Title && $ShowTitle %><h2 class="element__title">$Title</h2><% end_if %>
<% if $Content %><div class="element__content">$Content</div><% end_if %>

<% if $PostsList %>
    <div class="row element__blog__list">
        <% loop $PostsList %>
            <% include ElementBlogSummary %>
        <% end_loop %>
    </div>
    <div class="row mb-3">
        <div class="col-md-12 text-center">
            <p>
                <a href="$Blog.Link" class="btn btn-primary" title="View all $Title posts">
                    View all posts
                </a>
            </p>
        </div>
<% else %>
    <p>No recent posts.</p>
<% end_if %>
