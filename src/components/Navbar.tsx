import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { Logo } from './Logo';
import {
  BookOpen,
  Compass,
  Feather,
  LogOut,
  Moon,
  Search,
  Sun,
  UserCheck,
  Users,
  Menu,
  X,
  Library,
} from 'lucide-react';
import { StorageService } from '../services/storage';

interface NavbarProps {
  currentView: string;
  onNavigate: (view: string, param?: string) => void;
  onSearchSubmit?: (query: string) => void;
}

export const Navbar: React.FC<NavbarProps> = ({ currentView, onNavigate, onSearchSubmit }) => {
  const { user, logout, switchUser } = useAuth();
  const { isDark, toggleTheme } = useTheme();
  const [navSearch, setNavSearch] = useState('');
  const [showUserDropdown, setShowUserDropdown] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const allUsers = StorageService.getUsers();

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (navSearch.trim()) {
      onNavigate('books', navSearch.trim());
      if (onSearchSubmit) onSearchSubmit(navSearch.trim());
      setMobileMenuOpen(false);
    }
  };

  return (
    <header className="sticky top-0 z-40 border-b border-warmborder/80 bg-paper/95 backdrop-blur-sm dark:border-[#4B3035] dark:bg-wineblack/95">
      <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:px-8">
        {/* Brand Logo */}
        <button
          type="button"
          onClick={() => onNavigate(user ? 'home' : 'landing')}
          className="focus-ring group rounded-xl text-left"
          aria-label="Bookworm Home"
        >
          <Logo variant="full" tone="dark" />
        </button>

        {/* Global Search Bar (Desktop) */}
        <form onSubmit={handleSearch} className="hidden md:flex items-center relative max-w-xs w-full">
          <Search className="absolute left-3 h-4 w-4 text-tan" />
          <input
            type="text"
            placeholder="Search books, authors, titles…"
            value={navSearch}
            onChange={(e) => setNavSearch(e.target.value)}
            className="w-full rounded-full border border-warmborder bg-parchment/40 dark:bg-[#271519] dark:border-[#4B3035] pl-9 pr-4 py-1.5 text-xs text-ink dark:text-[#f4e9e2] placeholder-tan focus:border-burgundy focus:outline-hidden focus:ring-1 focus:ring-burgundy"
          />
        </form>

        {/* Desktop Navigation Links */}
        <nav className="hidden lg:flex items-center gap-1.5" aria-label="Main Navigation">
          <button
            type="button"
            onClick={() => onNavigate('books')}
            className={`focus-ring flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition ${
              currentView === 'books'
                ? 'bg-rosetint text-burgundy dark:bg-[#382329] dark:text-[#f4e9e2]'
                : 'text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50 dark:hover:bg-[#382329]/50'
            }`}
          >
            <Compass className="h-4 w-4" />
            <span>Explore Books</span>
          </button>

          {user && (
            <>
              <button
                type="button"
                onClick={() => onNavigate('profile', user.username)}
                className={`focus-ring flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition ${
                  currentView === 'profile'
                    ? 'bg-rosetint text-burgundy dark:bg-[#382329] dark:text-[#f4e9e2]'
                    : 'text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50 dark:hover:bg-[#382329]/50'
                }`}
              >
                <Library className="h-4 w-4" />
                <span>My Bookshelf</span>
              </button>

              <button
                type="button"
                onClick={() => onNavigate('feed')}
                className={`focus-ring flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition ${
                  currentView === 'feed'
                    ? 'bg-rosetint text-burgundy dark:bg-[#382329] dark:text-[#f4e9e2]'
                    : 'text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50 dark:hover:bg-[#382329]/50'
                }`}
              >
                <Feather className="h-4 w-4" />
                <span>Feed</span>
              </button>
            </>
          )}

          <button
            type="button"
            onClick={() => onNavigate('users')}
            className={`focus-ring flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition ${
              currentView === 'users'
                ? 'bg-rosetint text-burgundy dark:bg-[#382329] dark:text-[#f4e9e2]'
                : 'text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50 dark:hover:bg-[#382329]/50'
            }`}
          >
            <Users className="h-4 w-4" />
            <span>Meet Readers</span>
          </button>
        </nav>

        {/* Right Section: Theme Toggle + User Menu */}
        <div className="flex items-center gap-2">
          {/* Theme Toggle Button */}
          <button
            type="button"
            onClick={toggleTheme}
            aria-label="Toggle dark/light theme"
            className="focus-ring inline-flex h-9 w-9 items-center justify-center rounded-full border border-warmborder text-burgundy transition hover:bg-parchment dark:border-[#4B3035] dark:text-[#F4E9E2] dark:hover:bg-[#382329]"
          >
            {isDark ? <Sun className="h-4 w-4 text-gold" /> : <Moon className="h-4 w-4" />}
          </button>

          {/* User Profile / Reader Switcher */}
          {user ? (
            <div className="relative">
              <button
                type="button"
                onClick={() => setShowUserDropdown(!showUserDropdown)}
                className="focus-ring flex items-center gap-2 rounded-full border border-warmborder/80 bg-parchment/60 dark:bg-[#271519] dark:border-[#4B3035] p-1 pr-3 text-xs font-semibold text-ink dark:text-[#F4E9E2] hover:bg-rosetint/40"
              >
                <div className="h-7 w-7 rounded-full bg-burgundy text-[#FFF8EF] flex items-center justify-center text-xs font-serif font-bold uppercase">
                  {user.name.charAt(0)}
                </div>
                <span className="hidden sm:inline-block max-w-[100px] truncate">{user.name}</span>
              </button>

              {/* User Dropdown */}
              {showUserDropdown && (
                <div className="absolute right-0 mt-2 w-56 rounded-2xl border border-warmborder bg-paper p-2 shadow-xl dark:border-[#4B3035] dark:bg-raised z-50">
                  <div className="px-3 py-2 border-b border-warmborder/70 dark:border-[#4B3035]">
                    <p className="font-serif text-sm font-semibold text-ink dark:text-[#f4e9e2]">{user.name}</p>
                    <p className="text-xs text-tan dark:text-[#c7a491]">@{user.username}</p>
                  </div>

                  <div className="py-1">
                    <button
                      type="button"
                      onClick={() => {
                        onNavigate('profile', user.username);
                        setShowUserDropdown(false);
                      }}
                      className="w-full text-left rounded-lg px-3 py-1.5 text-xs text-walnut dark:text-[#d4c2b9] hover:bg-rosetint dark:hover:bg-[#382329]"
                    >
                      View my tree bookshelf
                    </button>
                    <button
                      type="button"
                      onClick={() => {
                        onNavigate('onboarding');
                        setShowUserDropdown(false);
                      }}
                      className="w-full text-left rounded-lg px-3 py-1.5 text-xs text-walnut dark:text-[#d4c2b9] hover:bg-rosetint dark:hover:bg-[#382329]"
                    >
                      Reading goals &amp; preferences
                    </button>
                  </div>

                  {/* Switch Demo Readers */}
                  <div className="border-t border-warmborder/70 dark:border-[#4B3035] pt-1.5 mt-1">
                    <p className="px-3 text-[10px] font-semibold uppercase tracking-wider text-tan">Switch Reader</p>
                    {allUsers.map((u) => (
                      <button
                        key={u.id}
                        type="button"
                        onClick={() => {
                          switchUser(u.id);
                          setShowUserDropdown(false);
                        }}
                        className={`w-full flex items-center justify-between rounded-lg px-3 py-1.5 text-xs ${
                          u.id === user.id
                            ? 'font-semibold text-burgundy dark:text-[#e4b3a5]'
                            : 'text-walnut dark:text-[#d4c2b9] hover:bg-rosetint dark:hover:bg-[#382329]'
                        }`}
                      >
                        <span>{u.name}</span>
                        {u.id === user.id && <UserCheck className="h-3.5 w-3.5" />}
                      </button>
                    ))}
                  </div>

                  <div className="border-t border-warmborder/70 dark:border-[#4B3035] pt-1.5 mt-1">
                    <button
                      type="button"
                      onClick={() => {
                        logout();
                        setShowUserDropdown(false);
                        onNavigate('landing');
                      }}
                      className="w-full flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30"
                    >
                      <LogOut className="h-3.5 w-3.5" />
                      <span>Log out</span>
                    </button>
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => onNavigate('login')}
                className="focus-ring rounded-full px-3.5 py-1.5 text-xs font-semibold text-burgundy hover:bg-rosetint dark:text-[#F4E9E2] dark:hover:bg-[#382329]"
              >
                Log in
              </button>
              <button
                type="button"
                onClick={() => onNavigate('register')}
                className="focus-ring rounded-full bg-burgundy px-4 py-1.5 text-xs font-semibold text-[#FFF8EF] shadow-xs transition hover:bg-burgundy-hover"
              >
                Join Bookworm
              </button>
            </div>
          )}

          {/* Mobile Menu Button */}
          <button
            type="button"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="lg:hidden focus-ring inline-flex h-9 w-9 items-center justify-center rounded-lg border border-warmborder text-walnut dark:border-[#4B3035] dark:text-[#f4e9e2]"
            aria-label="Open navigation menu"
          >
            {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu Drawer */}
      {mobileMenuOpen && (
        <div className="lg:hidden border-t border-warmborder/80 bg-paper p-4 dark:border-[#4B3035] dark:bg-wineblack">
          <form onSubmit={handleSearch} className="mb-4 flex items-center relative">
            <Search className="absolute left-3 h-4 w-4 text-tan" />
            <input
              type="text"
              placeholder="Search books, authors…"
              value={navSearch}
              onChange={(e) => setNavSearch(e.target.value)}
              className="w-full rounded-full border border-warmborder bg-parchment/40 pl-9 pr-4 py-2 text-xs text-ink dark:bg-[#271519] dark:border-[#4B3035] dark:text-[#f4e9e2]"
            />
          </form>

          <nav className="flex flex-col gap-2">
            <button
              type="button"
              onClick={() => {
                onNavigate('books');
                setMobileMenuOpen(false);
              }}
              className="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-walnut dark:text-[#d4c2b9] hover:bg-rosetint"
            >
              <Compass className="h-4 w-4" />
              <span>Explore Books</span>
            </button>

            {user && (
              <>
                <button
                  type="button"
                  onClick={() => {
                    onNavigate('profile', user.username);
                    setMobileMenuOpen(false);
                  }}
                  className="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-walnut dark:text-[#d4c2b9] hover:bg-rosetint"
                >
                  <Library className="h-4 w-4" />
                  <span>My Bookshelf</span>
                </button>

                <button
                  type="button"
                  onClick={() => {
                    onNavigate('feed');
                    setMobileMenuOpen(false);
                  }}
                  className="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-walnut dark:text-[#d4c2b9] hover:bg-rosetint"
                >
                  <Feather className="h-4 w-4" />
                  <span>Reading Feed</span>
                </button>
              </>
            )}

            <button
              type="button"
              onClick={() => {
                onNavigate('users');
                setMobileMenuOpen(false);
              }}
              className="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-walnut dark:text-[#d4c2b9] hover:bg-rosetint"
            >
              <Users className="h-4 w-4" />
              <span>Meet Readers</span>
            </button>
          </nav>
        </div>
      )}
    </header>
  );
};
