/**
 * Input Component
 * 
 * Standardized text input field with consistent styling.
 * Supports error state for validation feedback.
 * 
 * @example
 * <Input 
 *   type="email" 
 *   placeholder="Enter email" 
 *   error={errors.email}
 * />
 */

const Input = ({ 
  type = 'text',
  error = false,
  className = '',
  ...props 
}) => {
  const baseStyles = 'w-full px-4 py-2.5 rounded-lg border transition-colors outline-none';
  const normalStyles = 'border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
  const errorStyles = 'border-red-300 focus:ring-2 focus:ring-red-500 focus:border-red-500';
  
  return (
    <input
      type={type}
      className={`${baseStyles} ${error ? errorStyles : normalStyles} ${className}`}
      {...props}
    />
  );
};

export default Input;

