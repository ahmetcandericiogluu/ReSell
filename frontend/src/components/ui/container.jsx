/**
 * Container Component
 * 
 * Centered page container with consistent max-width and padding.
 * Used as the main layout wrapper for pages.
 * 
 * @example
 * <Container>
 *   <h1>Page Title</h1>
 *   ...content
 * </Container>
 * 
 * <Container size="sm">
 *   ...narrow content like forms
 * </Container>
 */

const Container = ({ 
  children,
  size = 'default',
  className = '',
  ...props 
}) => {
  const baseStyles = 'mx-auto px-4 sm:px-6 lg:px-8';
  
  const sizes = {
    sm: 'max-w-2xl',      // For forms, auth pages
    default: 'max-w-7xl', // For main content
    full: 'max-w-full',   // Full width
  };
  
  const sizeClass = sizes[size] || sizes.default;
  
  return (
    <div 
      className={`${baseStyles} ${sizeClass} ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export default Container;

