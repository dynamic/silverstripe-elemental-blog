<% if $Title && $ShowTitle %><h2 class="element__title">$Title</h2><% end_if %>
<% if $Content %><div class="element__content">$Content</div><% end_if %>

<% if $PostsList %>
    <% loop $PostsList %>
        <h3>
            <a href="$Link.ATT" title="Go to the $Title post">
                $Title
            </a>
        </h3>

        <% include SilverStripe\\Blog\\EntryMeta %>

        <% if $Summary %>
            $Summary
        <% else %>
            <p>$Excerpt</p>
        <% end_if %>
    <% end_loop %>
    <p><a href="$Blog.Link" class="btn btn-primary" title="Go to the $Title page">View all posts</a></p>
<% end_if %>

