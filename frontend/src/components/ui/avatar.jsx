/**
 * Avatar Component
 * 
 * Simple circular avatar showing user initials.
 * Used for user profiles, seller info, etc.
 * 
 * @example
 * <Avatar name="John Doe" />
 * <Avatar name="Jane Smith" size="lg" />
 */

const Avatar = ({ 
  name = '',
  size = 'md',
  className = '',
  ...props 
}) => {
  const baseStyles = 'rounded-full flex items-center justify-center flex-shrink-0 bg-primary-100 text-primary-700 font-semibold';
  
  const sizes = {
    sm: 'w-8 h-8 text-sm',
    md: 'w-12 h-12 text-lg',
    lg: 'w-16 h-16 text-2xl',
  };
  
  const sizeClass = sizes[size] || sizes.md;
  
  // Get initials from name
  const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
      return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name[0].toUpperCase();
  };
  
  return (
    <div 
      className={`${baseStyles} ${sizeClass} ${className}`}
      {...props}
    >
      {getInitials(name)}
    </div>
  );
};

export default Avatar;

