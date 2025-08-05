export const getFileName = (doc) => {
  try {
    if (typeof doc === 'object' && doc && doc.name) {
      return doc.name;
    }
    if (typeof doc === 'string' && doc.length > 0) {
      return doc.split('/').pop() || 'Unknown file';
    }
    return 'Unknown file';
  } catch (error) {
    console.error('Error getting filename:', error);
    return 'Unknown file';
  }
};
