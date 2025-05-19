<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Book</title>
</head>
<body>
    <h1>Create Book</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <ul style="color: red;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Title:</label><br>
        <input type="text" name="title" value="{{ old('title') }}"><br><br>

        <label>Author:</label><br>
        <input type="text" name="author" value="{{ old('author') }}"><br><br>

        <label>Description:</label><br>
        <textarea name="description">{{ old('description') }}</textarea><br><br>

        <label>Published Date:</label><br>
        <input type="date" name="published_date" value="{{ old('published_date') }}"><br><br>

        <label>Discount (%):</label><br>
        <input type="number" name="discount" value="{{ old('discount') }}" min="0" max="100"><br><br>

        <label>Quantity:</label><br>
        <input type="number" name="quantity" value="{{ old('quantity') }}" min="0"><br><br>

        <label>Price ($):</label><br>
        <input type="number" step="0.01" name="price" value="{{ old('price') }}"><br><br>

        <label>Image URL (optional):</label><br>
        <input type="url" name="url_image" value="{{ old('url_image') }}"><br><br>

        <label>Upload Image:</label><br>
        <input type="file" name="path_image" accept="image/*"><br><br>

        <label>Languages:</label><br>
        <label><input type="checkbox" name="languages[]" value="English"> English</label><br>
        <label><input type="checkbox" name="languages[]" value="French"> French</label><br>
        <label><input type="checkbox" name="languages[]" value="Khmer"> Khmer</label><br><br>

        <label>Category:</label><br>
        <select name="category_id">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Create Book</button>
    </form>
</body>
</html>
