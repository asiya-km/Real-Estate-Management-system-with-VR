import React, { useState } from 'react';
import Button from '../ui/Button';
import Input from '../ui/Input';
import { EyeIcon, EyeOffIcon, UserCircle as LoaderCircle } from 'lucide-react';
import SocialLogin from './SocialLogin';

const LoginForm: React.FC = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState<{email?: string; password?: string}>({});

  const validateForm = () => {
    const newErrors: {email?: string; password?: string} = {};
    
    if (!email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Email is invalid';
    }
    
    if (!password) {
      newErrors.password = 'Password is required';
    } else if (password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) return;
    
    setIsLoading(true);
    
    // Simulate API call
    try {
      await new Promise(resolve => setTimeout(resolve, 1500));
      // For demo purposes, this would be where you'd handle the actual login logic
      console.log('Login successful');
      // Redirect or update auth state would happen here
    } catch (error) {
      console.error('Login failed:', error);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="space-y-4">
        <Input
          label="Email"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="your@email.com"
          error={errors.email}
          disabled={isLoading}
        />
        
        <div className="relative">
          <Input
            label="Password"
            type={showPassword ? 'text' : 'password'}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            error={errors.password}
            disabled={isLoading}
          />
          <button
            type="button"
            className="absolute right-3 top-[38px] text-gray-500 hover:text-blue-800 transition-colors"
            onClick={() => setShowPassword(!showPassword)}
          >
            {showPassword ? <EyeOffIcon size={18} /> : <EyeIcon size={18} />}
          </button>
        </div>
      </div>
      
      <div className="flex items-center justify-between">
        <label className="flex items-center space-x-2 cursor-pointer">
          <input
            type="checkbox"
            checked={rememberMe}
            onChange={() => setRememberMe(!rememberMe)}
            className="w-4 h-4 text-blue-800 rounded focus:ring-blue-700"
            disabled={isLoading}
          />
          <span className="text-sm text-gray-600">Remember me</span>
        </label>
        
        <a 
          href="#" 
          className="text-sm text-blue-800 hover:text-blue-950 transition-colors"
        >
          Forgot password?
        </a>
      </div>
      
      <Button
        type="submit"
        fullWidth
        isLoading={isLoading}
        disabled={isLoading}
      >
        {isLoading ? 'Signing in...' : 'Sign in'}
      </Button>
      
      <div className="relative flex items-center justify-center mt-6">
        <div className="border-t border-gray-300 absolute w-full"></div>
        <div className="bg-white px-4 relative z-10 text-sm text-gray-500">or continue with</div>
      </div>
      
      <SocialLogin isLoading={isLoading} />
      
      <div className="text-center mt-6">
        <p className="text-gray-600">
          Don't have an account?{' '}
          <a href="#" className="text-blue-800 hover:text-blue-950 font-medium transition-colors">
            Create account
          </a>
        </p>
      </div>
    </form>
  );
};

export default LoginForm;