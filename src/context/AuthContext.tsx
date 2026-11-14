import React, { createContext, useContext, useState, useEffect } from 'react';
import { User } from '../types';
import { StorageService } from '../services/storage';

interface AuthContextType {
  user: User | null;
  login: (usernameOrEmail: string) => boolean;
  register: (name: string, username: string, email: string) => User;
  logout: () => void;
  updateProfile: (updates: Partial<User>) => void;
  completeOnboarding: (genres: string[], yearlyGoal: number) => void;
  switchUser: (userId: string) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(() => StorageService.getCurrentUser());

  useEffect(() => {
    const current = StorageService.getCurrentUser();
    setUser(current);
  }, []);

  const login = (usernameOrEmail: string): boolean => {
    const users = StorageService.getUsers();
    const query = usernameOrEmail.trim().toLowerCase();
    const found = users.find(
      (u) => u.username.toLowerCase() === query || u.email.toLowerCase() === query
    );
    if (found) {
      StorageService.setCurrentUser(found.id);
      setUser(found);
      return true;
    }
    return false;
  };

  const register = (name: string, username: string, email: string): User => {
    const newUser: User = {
      id: `user-${Date.now()}`,
      name: name.trim(),
      username: username.trim().toLowerCase(),
      email: email.trim().toLowerCase(),
      bio: 'New reader at Bookworm.',
      yearly_reading_goal: 12,
      preferred_genres: [],
      onboarding_completed_at: null,
      created_at: new Date().toISOString(),
    };
    StorageService.addUser(newUser);
    StorageService.setCurrentUser(newUser.id);
    setUser(newUser);
    return newUser;
  };

  const logout = () => {
    StorageService.setCurrentUser(null);
    setUser(null);
  };

  const updateProfile = (updates: Partial<User>) => {
    if (!user) return;
    const updated = StorageService.updateUser(user.id, updates);
    setUser(updated);
  };

  const completeOnboarding = (genres: string[], yearlyGoal: number) => {
    if (!user) return;
    const updated = StorageService.updateUser(user.id, {
      preferred_genres: genres,
      yearly_reading_goal: yearlyGoal,
      onboarding_completed_at: new Date().toISOString(),
    });
    setUser(updated);
  };

  const switchUser = (userId: string) => {
    StorageService.setCurrentUser(userId);
    const u = StorageService.getUserById(userId) || null;
    setUser(u);
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        login,
        register,
        logout,
        updateProfile,
        completeOnboarding,
        switchUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used within AuthProvider');
  return context;
};
