# Clickable Table Rows

This feature allows any table row to be clickable and navigate to a specific URL. It's implemented globally in the admin layout and can be easily applied to any table.

## How to Use

### 1. Add the CSS Classes and Attributes

Add the following attributes to any `<tr>` element you want to make clickable:

```html
<tr class="clickable-row" 
    data-href="{{ route('admin.content-types.show', $item->id) }}"
    style="cursor: pointer;">
    <!-- Your table cells here -->
</tr>
```

### 2. Required Attributes

- **`class="clickable-row"`** - This class is required to identify clickable rows
- **`data-href="URL"`** - The URL to navigate to when the row is clicked
- **`style="cursor: pointer;"`** - Optional but recommended for better UX

### 3. Example Implementation

```html
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr class="clickable-row" 
                data-href="{{ route('admin.items.show', $item->id) }}"
                style="cursor: pointer;">
                <td>{{ $item->name }}</td>
                <td>{{ $item->description }}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary">Edit</button>
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

## Features

### Smart Click Detection
- The row click is ignored when clicking on interactive elements like:
  - Buttons (`button`)
  - Links (`a`)
  - Form inputs (`input`, `select`, `textarea`)
  - Button groups (`.btn-group`)
  - Form switches (`.form-check`, `.form-switch`)

### Visual Feedback
- Rows have a hover effect that changes the background color
- Smooth transition animation for better UX
- Cursor changes to pointer on hover

### Universal Implementation
- Works across all admin pages
- No additional JavaScript needed per page
- Automatically handles dynamic content

## Best Practices

1. **Always include the cursor pointer style** for better UX
2. **Use meaningful URLs** in the `data-href` attribute
3. **Test with interactive elements** to ensure they still work
4. **Consider mobile users** - ensure touch targets are large enough

## Troubleshooting

### Row not clickable?
- Check that the `clickable-row` class is present
- Verify the `data-href` attribute has a valid URL
- Ensure no JavaScript errors in the console

### Buttons not working?
- Make sure buttons are properly structured
- Check that buttons are not inside elements that might interfere

### Hover effect not working?
- Verify the row has the `clickable-row` class
- Check for CSS conflicts that might override the hover styles 