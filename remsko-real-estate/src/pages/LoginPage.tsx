import React from 'react';
import LoginForm from '../components/login/LoginForm';
import Logo from '../components/common/Logo';
import { Building2 } from 'lucide-react';

const LoginPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-900/90 to-blue-950/90 flex flex-col items-center justify-center p-4 md:p-6 relative overflow-hidden">
      {/* Background image with overlay */}
      <div 
        className="absolute inset-0 bg-cover bg-center z-0 opacity-30" 
        style={{ 
          backgroundImage: "url('https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')",
          backgroundBlendMode: "overlay"
        }} 
      />
      
      {/* Content container */}
      <div className="w-full max-w-md z-10">
        {/* Logo and title */}
        <div className="text-center mb-8 transform transition duration-500 hover:scale-105">
          <Logo />
          <h1 className="text-3xl font-bold mt-4 text-white">EstateManager</h1>
          <p className="text-blue-200 mt-2">Professional Real Estate Management</p>
        </div>
        
        {/* Login form card */}
        <div className="bg-white bg-opacity-95 backdrop-blur-sm rounded-xl shadow-2xl p-6 md:p-8 transition-all duration-300 hover:shadow-blue-900/20">
          <h2 className="text-2xl font-semibold text-blue-950 mb-6">Welcome Back</h2>
          <LoginForm />
        </div>
        
        {/* Footer */}
        <div className="mt-8 text-center text-blue-200 text-sm">
          <p>© 2025 EstateManager. All rights reserved.</p>
          <div className="mt-2 space-x-4">
            <a href="#" className="hover:text-white transition-colors">Terms</a>
            <a href="#" className="hover:text-white transition-colors">Privacy</a>
            <a href="#" className="hover:text-white transition-colors">Support</a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;