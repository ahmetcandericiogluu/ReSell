/**
 * Textarea Component
 * 
 * Standardized textarea field with consistent styling.
 * Supports error state and prevents manual resizing.
 * 
 * @example
 * <Textarea 
 *   rows={6} 
 *   placeholder="Enter description" 
 *   error={errors.description}
 * />
 */

const Textarea = ({ 
  error = false,
  className = '',
  rows = 4,
  ...props 
}) => {
  const baseStyles = 'w-full px-4 py-2.5 rounded-lg border transition-colors outline-none resize-none';
  const normalStyles = 'border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
  const errorStyles = 'border-red-300 focus:ring-2 focus:ring-red-500 focus:border-red-500';
  
  return (
    <textarea
      rows={rows}
      className={`${baseStyles} ${error ? errorStyles : normalStyles} ${className}`}
      {...props}
    />
  );
};

export default Textarea;

