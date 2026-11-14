<div align="center">

<img src="public/images/logo.png" alt="Bookworm logo" width="140" />

<h1>📚 Bookworm</h1>

<p><b>A cozy community for book lovers. Read, share, and grow your own bookshelf tree.</b></p>

<p>
  <img src="https://img.shields.io/badge/Laravel-800020?style=for-the-badge&logo=laravel&logoColor=F5EBDD" alt="Laravel" />
  <img src="https://img.shields.io/badge/Blade-800020?style=for-the-badge&logo=laravel&logoColor=F5EBDD" alt="Blade" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-800020?style=for-the-badge&logo=tailwindcss&logoColor=F5EBDD" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/PHP-800020?style=for-the-badge&logo=php&logoColor=F5EBDD" alt="PHP" />
</p>

<p>
  <a href="#-features">Features</a> •
  <a href="#-the-bookshelf-tree">Bookshelf Tree</a> •
  <a href="#-screenshots">Screenshots</a> •
  <a href="#-getting-started">Getting Started</a></p>

</div>

<hr />

<h2>🌿 About</h2>

<p>
  <b>Bookworm</b> is a book community website built with <b>Laravel</b>, <b>Blade</b> and <b>Tailwind CSS</b>.
  Readers can build a profile, keep track of what they read, and connect with other readers,
  all in a warm <b>burgundy & cream</b> design that feels like an old library.
</p>

<h2>✨ Features</h2>

<table>
  <tr>
    <td width="50%" valign="top">
      <h3>🌳 Bookshelf Tree</h3>
      <p>Every profile has a tree-shaped bookshelf that grows as you add books.</p>
    </td>
    <td width="50%" valign="top">
      <h3>👤 Reader Profiles</h3>
      <p>Your personal space to show what you read and love.</p>
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top">
      <h3>💬 Community</h3>
      <p>Discover other readers and share your favourite books.</p>
    </td>
    <td width="50%" valign="top">
      <h3>🎨 Library Aesthetic</h3>
      <p>A burgundy & cream theme built with Tailwind CSS.</p>
    </td>
  </tr>
</table>

<h2>🌳 The Bookshelf Tree</h2>

<p>The profile page's centrepiece. Your books form a living tree:</p>

<table>
  <tr>
    <th align="left">Part</th>
    <th align="left">What it does</th>
  </tr>
  <tr>
    <td>🍃 <b>Crown</b></td>
    <td>The top of the tree, filled with books as your collection grows</td>
  </tr>
  <tr>
    <td>🪵 <b>Trunk</b></td>
    <td>Stacked shelf sections that hold the body of your library</td>
  </tr>
  <tr>
    <td>🌱 <b>Base</b></td>
    <td>The roots, where every reader's tree begins</td>
  </tr>
</table>

<p><i>The more you read, the bigger your tree gets.</i></p>

<h2>📸 Screenshots</h2>

<div align="center">
  <img src="docs/screenshots/home.png" alt="Home page" width="80%" />
  <br /><sub><b>Home page</b></sub>
  <br /><br />
  <img src="docs/screenshots/profile-tree.png" alt="Profile with bookshelf tree" width="80%" />
  <br /><sub><b>Profile and bookshelf tree</b></sub>
</div>

<h2>🚀 Getting Started</h2>

<h3>Requirements</h3>
<ul>
  <li>PHP 8.2+</li>
  <li>Composer</li>
  <li>Node.js & npm</li>
  <li>MySQL (or another database supported by Laravel)</li>
</ul>

<h3>Installation</h3>

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/bookworm.git
cd bookworm

# 2. Install dependencies
composer install
npm install

# 3. Set up the environment
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env, then run migrations
php artisan migrate

# 5. Link storage (for book covers & avatars)
php artisan storage:link

# 6. Build assets and start the server
npm run dev
php artisan serve
```

<p>Then open <code>http://localhost:8000</code> 🎉</p>

<h2>🗂️ Project Structure</h2>

```
bookworm/
├── app/                 # Models, controllers, logic
├── database/            # Migrations & seeders
├── resources/
│   ├── views/           # Blade templates
│   └── css/             # Tailwind styles
├── routes/web.php       # Web routes
└── public/              # Public assets (logo, images)
```

<h2>🤝 Contributing</h2>

<p>Ideas and pull requests are welcome! Open an issue to discuss what you'd like to change.</p>

<hr />

<div align="center">
  <sub>Made with ❤️ and lots of books 📖</sub>
  <br />
  <img src="https://img.shields.io/badge/made_for-readers-800020?style=flat-square" alt="Made for readers" />
</div>
