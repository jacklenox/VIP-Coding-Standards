<?php
/**
 * WordPress-VIP-Minimum Coding Standard.
 *
 * @link https://github.com/Automattic/VIP-Coding-Standards
 */

/**
 * Restricts usage of some constants
 */
class WordPressVIPMinimum_Sniffs_Constants_ConstantRestrictionsSniff implements PHP_CodeSniffer_Sniff {

	public $restrictedConstantNames = array(
		'A8C_PROXIED_REQUEST',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_CONSTANT_ENCAPSED_STRING,
		);
	}// end register()

	/**
	 * Process this test when one of its tokens is encoutnered
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int				   $stackPtr  The position of the current token in the stack passed in $tokens
	 *
	 * $return void
	 */ 
	public function process( PHP_CodeSniffer_File $phpcsFile, $stackPtr ) {

		$tokens = $phpcsFile->getTokens();

		$constantName = trim( $tokens[$stackPtr]['content'], "\"'" );

		if ( false === in_array( $constantName, $this->restrictedConstantNames, true ) ) {
			// Not the constant we are looking for
			return;
		}

		// Find the previou non-empty token.
		$openBracket = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true, null, true);

		if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
			// Not a function call.
			return;
		}

		if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
			// Not a function call.
			return;
		}

		// Find the previous non-empty token.
		$search   = PHP_CodeSniffer_Tokens::$emptyTokens;
		$search[] = T_BITWISE_AND;
		$previous = $phpcsFile->findPrevious($search, ($openBracket - 1), null, true);
		if ($tokens[$previous]['code'] === T_FUNCTION) {
			// It's a function definition, not a function call.
			return;
		}

		if ( true === in_array( $tokens[$previous]['code'], PHP_CodeSniffer_Tokens::$functionNameTokens, true ) ) {
			if ( 'define' === $tokens[$previous]['content'] ) {
				$phpcsFile->addError( sprintf( "The definition of %s constant is prohibied. Please use a different name.", $constantName ), $previous );
			} else {
				$phpcsFile->addWarning( sprintf( "Code is touching the %s constant. Make sure it's used appropriately", $constantName ), $previous );
			}
		}
	}

} // End class.