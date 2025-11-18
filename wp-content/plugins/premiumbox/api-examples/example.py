#!/usr/bin/env python3
"""
PremiumBox API Python Client
A simple Python client for interacting with the PremiumBox API.

Requirements:
    pip install requests

Usage:
    python example.py
"""

import requests
import json
from typing import Dict, Any, Optional


class PremiumBoxAPI:
    """PremiumBox API Client"""
    
    def __init__(self, base_url: str, api_login: str, api_key: str, api_lang: str = 'en'):
        """
        Initialize API client
        
        Args:
            base_url: Base API URL (e.g., 'https://your-domain.com/api/v1/')
            api_login: API login credential
            api_key: API key credential
            api_lang: Language code for responses
        """
        self.base_url = base_url.rstrip('/') + '/'
        self.api_login = api_login
        self.api_key = api_key
        self.api_lang = api_lang
        self.session = requests.Session()
        
    def _get_headers(self) -> Dict[str, str]:
        """Get request headers with authentication"""
        return {
            'API-Login': self.api_login,
            'API-Key': self.api_key,
            'API-Lang': self.api_lang
        }
    
    def request(self, endpoint: str, params: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        """
        Make API request
        
        Args:
            endpoint: API endpoint name
            params: Request parameters
            
        Returns:
            API response as dictionary
        """
        url = self.base_url + endpoint
        
        try:
            response = self.session.post(
                url,
                data=params or {},
                headers=self._get_headers(),
                timeout=30
            )
            
            response.raise_for_status()
            return response.json()
            
        except requests.exceptions.RequestException as e:
            return {
                'error': 'request_error',
                'error_text': str(e),
                'data': {}
            }
        except json.JSONDecodeError as e:
            return {
                'error': 'json_error',
                'error_text': str(e),
                'data': {}
            }
    
    def test_connection(self) -> Dict[str, Any]:
        """Test API connection"""
        return self.request('test')
    
    def get_currencies(self, currency_id_give: Optional[int] = None, 
                      currency_id_get: Optional[int] = None) -> Dict[str, Any]:
        """Get available currencies"""
        params = {}
        if currency_id_give:
            params['currency_id_give'] = currency_id_give
        if currency_id_get:
            params['currency_id_get'] = currency_id_get
        return self.request('get_direction_currencies', params)
    
    def get_directions(self, currency_id_give: Optional[int] = None,
                      currency_id_get: Optional[int] = None) -> Dict[str, Any]:
        """Get exchange directions"""
        params = {}
        if currency_id_give:
            params['currency_id_give'] = currency_id_give
        if currency_id_get:
            params['currency_id_get'] = currency_id_get
        return self.request('get_directions', params)
    
    def get_direction(self, direction_id: Optional[int] = None,
                     currency_id_give: Optional[int] = None,
                     currency_id_get: Optional[int] = None) -> Dict[str, Any]:
        """Get direction details"""
        params = {}
        if direction_id:
            params['direction_id'] = direction_id
        if currency_id_give:
            params['currency_id_give'] = currency_id_give
        if currency_id_get:
            params['currency_id_get'] = currency_id_get
        return self.request('get_direction', params)
    
    def calculate(self, direction_id: int, amount: float, 
                 calc_action: int = 1) -> Dict[str, Any]:
        """
        Calculate exchange
        
        Args:
            direction_id: Direction ID
            amount: Amount to calculate
            calc_action: 1 for give amount, 2 for get amount
        """
        return self.request('get_calc', {
            'direction_id': direction_id,
            'calc_amount': amount,
            'calc_action': calc_action
        })
    
    def create_transaction(self, direction_id: int, amount: float,
                          calc_action: int, fields: Dict[str, Any],
                          callback_url: Optional[str] = None) -> Dict[str, Any]:
        """
        Create exchange transaction
        
        Args:
            direction_id: Direction ID
            amount: Transaction amount
            calc_action: 1 for give amount, 2 for get amount
            fields: Additional required fields
            callback_url: Optional callback URL
        """
        params = {
            'direction_id': direction_id,
            'calc_amount': amount,
            'calc_action': calc_action,
            **fields
        }
        if callback_url:
            params['callback_url'] = callback_url
        
        return self.request('create_bid', params)
    
    def get_transaction(self, transaction_id: Optional[int] = None,
                       transaction_hash: Optional[str] = None) -> Dict[str, Any]:
        """Get transaction information"""
        params = {}
        if transaction_id:
            params['id'] = transaction_id
        if transaction_hash:
            params['hash'] = transaction_hash
        return self.request('bid_info', params)
    
    def cancel_transaction(self, transaction_id: Optional[int] = None,
                          transaction_hash: Optional[str] = None) -> Dict[str, Any]:
        """Cancel transaction"""
        params = {}
        if transaction_id:
            params['id'] = transaction_id
        if transaction_hash:
            params['hash'] = transaction_hash
        return self.request('cancel_bid', params)


def main():
    """Example usage"""
    
    # Initialize API client
    api = PremiumBoxAPI(
        base_url='https://your-domain.com/api/v1/',
        api_login='your_api_login_here',
        api_key='your_api_key_here',
        api_lang='en'
    )
    
    print("PremiumBox API Python Client Example")
    print("=" * 50)
    
    # Test connection
    print("\n1. Testing connection...")
    result = api.test_connection()
    if result['error'] == '0':
        print(f"✓ Connected! User ID: {result['data']['user_id']}")
        print(f"  IP: {result['data']['ip']}")
    else:
        print(f"✗ Connection failed: {result['error_text']}")
        return
    
    # Get currencies
    print("\n2. Getting available currencies...")
    result = api.get_currencies()
    if result['error'] == '0':
        give_count = len(result['data']['give'])
        get_count = len(result['data']['get'])
        print(f"✓ Found {give_count} source and {get_count} destination currencies")
        
        if give_count > 0:
            print(f"  Example: {result['data']['give'][0]['title']}")
    else:
        print(f"✗ Error: {result['error_text']}")
    
    # Calculate exchange
    print("\n3. Calculating exchange...")
    result = api.calculate(
        direction_id=1,
        amount=100,
        calc_action=1
    )
    if result['error'] == '0':
        data = result['data']
        print(f"✓ You send: {data['sum_give_com']} {data['currency_code_give']}")
        print(f"  You get: {data['sum_get_com']} {data['currency_code_get']}")
        print(f"  Rate: {data['course_give']}")
    else:
        print(f"✗ Error: {result['error_text']}")
    
    print("\n" + "=" * 50)


if __name__ == '__main__':
    main()
