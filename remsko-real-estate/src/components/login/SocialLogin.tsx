import React from 'react';
import { GithubIcon, TwitterIcon } from 'lucide-react';

interface SocialLoginProps {
  isLoading: boolean;
}

const SocialLogin: React.FC<SocialLoginProps> = ({ isLoading }) => {
  return (
    <div className="grid grid-cols-3 gap-3">
      <button
        type="button"
        disabled={isLoading}
        className="flex justify-center items-center py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
      >
        <img src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png" alt="Google" className="w-5 h-5" />
      </button>
      
      <button
        type="button"
        disabled={isLoading}
        className="flex justify-center items-center py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
      >
        <TwitterIcon size={20} className="text-blue-500" />
      </button>
      
      <button
        type="button"
        disabled={isLoading}
        className="flex justify-center items-center py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
      >
        <GithubIcon size={20} />
      </button>
    </div>
  );
};

export default SocialLogin;