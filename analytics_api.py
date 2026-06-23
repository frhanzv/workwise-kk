from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional, Dict, Any
import pandas as pd
from datetime import datetime
import requests
import json
from contextlib import asynccontextmanager

# ============================================================================
# AI CONFIGURATION - GROQ (FAST & FREE!)
# ============================================================================

import os
from dotenv import load_dotenv

load_dotenv()

GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
GROQ_URL = "https://api.groq.com/openai/v1/chat/completions"
GROQ_MODEL = "llama-3.3-70b-versatile"

def test_groq():
    """Test if Groq API key is working"""
    try:
        headers = {
            "Authorization": f"Bearer {GROQ_API_KEY}",
            "Content-Type": "application/json"
        }
        
        response = requests.post(
            GROQ_URL,
            headers=headers,
            json={
                "model": GROQ_MODEL,
                "messages": [{"role": "user", "content": "Hi"}],
                "max_tokens": 10
            },
            timeout=5
        )
        
        if response.status_code == 200:
            print("✅ Groq API is ready!")
            return True
        else:
            print(f"⚠️ Groq API error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Cannot connect to Groq: {str(e)}")
        return False

# ============================================================================
# FASTAPI APP SETUP
# ============================================================================

@asynccontextmanager
async def lifespan(app: FastAPI):
    print("=" * 60)
    print("🚀 Workwise Analytics API Starting... v10.0 - FIXED!")
    print("=" * 60)
    test_groq()
    print(f"⚡ Using Groq model: {GROQ_MODEL}")
    print(f"🔧 NEW: Better handling of small/incomplete datasets")
    print(f"✅ Production queries work with ANY amount of data")
    print(f"🔗 API endpoint: http://localhost:8001")
    print("=" * 60)
    yield

app = FastAPI(title="Workwise Analytics API v10.0", lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ============================================================================
# MODELS
# ============================================================================

class ChatMessage(BaseModel):
    message: str
    data_source: Optional[str] = "workers"
    data: Optional[Dict[str, Any]] = None
    context: Optional[Dict[str, Any]] = None

class ChatResponse(BaseModel):
    response: str
    timestamp: str
    data_source: str

# ============================================================================
# CALCULATION FUNCTIONS
# ============================================================================

def calculate_ole_metrics(df: pd.DataFrame) -> pd.DataFrame:
    """
    Calculate Overall Labor Effectiveness (OLE) with improved error handling
    """
    
    # Check if DataFrame is empty
    if df.empty:
        print("⚠️ Empty DataFrame received for OLE calculation")
        return df
    
    # Define required columns with defaults
    numeric_columns = {
        'actual_work_time': 0,
        'scheduled_hours': 8.0,
        'units_produced': 0,
        'standard_output_rate': 0,
        'good_units': 0,
        'defective_units': 0,
        'downtime_minutes': 0
    }
    
    # Add missing columns if they don't exist
    for col, default in numeric_columns.items():
        if col not in df.columns:
            print(f"⚠️ Missing column '{col}' in OLE calculation, adding with default: {default}")
            df[col] = default
    
    # Convert to numeric with better error handling
    for col, default in numeric_columns.items():
        try:
            df[col] = pd.to_numeric(df[col], errors='coerce')
            nan_count = df[col].isna().sum()
            if nan_count > 0:
                print(f"⚠️ {nan_count} invalid values in '{col}', replacing with {default}")
            df[col] = df[col].fillna(default)
            df[col] = df[col].clip(lower=0)
        except Exception as e:
            print(f"❌ Error converting '{col}': {str(e)}")
            df[col] = default
    
    # For OLE calculation, we need actual_work_time and scheduled_hours
    # If actual_work_time is missing but we have productive_hours, use that
    if 'productive_hours' in df.columns:
        df['productive_hours'] = pd.to_numeric(df['productive_hours'], errors='coerce').fillna(0)
        df['actual_work_time'] = df['actual_work_time'].fillna(df['productive_hours'])
    
    # Validate data before calculation
    df_valid = df[
        (df['actual_work_time'] > 0) & 
        (df['scheduled_hours'] > 0)
    ].copy()
    
    if len(df_valid) == 0:
        print("⚠️ No valid records for OLE calculation (missing actual_work_time)")
        # Add default columns to original df
        for col in ['availability', 'performance', 'quality', 'ole', 'ole_category', 'expected_output']:
            df[col] = 0
        return df
    
    # Calculate Availability
    effective_work_time = df_valid['actual_work_time'] - (df_valid['downtime_minutes'] / 60.0)
    df_valid['availability'] = (effective_work_time / df_valid['scheduled_hours']).clip(0, 1)
    
    # Calculate expected output based on standard rate
    df_valid['expected_output'] = df_valid['standard_output_rate'] * df_valid['actual_work_time']
    
    # Calculate Performance - WITH SAFE DIVISION
    df_valid['performance'] = df_valid.apply(
        lambda row: (row['units_produced'] / row['expected_output']) if row['expected_output'] > 0 else 0,
        axis=1
    ).clip(0, 2)
    
    # Calculate Quality - WITH SAFE DIVISION
    df_valid['quality'] = df_valid.apply(
        lambda row: ((row['units_produced'] - row['defective_units']) / row['units_produced']) 
        if row['units_produced'] > 0 else 1,
        axis=1
    ).clip(0, 1)
    
    # Calculate OLE
    df_valid['ole'] = (df_valid['availability'] * df_valid['performance'] * df_valid['quality']) * 100
    
    # Categorize OLE performance
    df_valid['ole_category'] = df_valid['ole'].apply(lambda x: 
        '⭐ Excellent (>85%)' if x > 85 else
        '✅ Good (70-85%)' if x > 70 else
        '⚠️ Fair (50-70%)' if x > 50 else
        '❌ Poor (<50%)'
    )
    
    # Merge back to original dataframe
    ole_columns = ['availability', 'performance', 'quality', 'ole', 'ole_category', 'expected_output']
    for col in ole_columns:
        if col in df_valid.columns:
            df[col] = df[col] if col in df.columns else 0
            df.loc[df_valid.index, col] = df_valid[col]
    
    return df


def calculate_lost_productivity(df: pd.DataFrame) -> pd.DataFrame:
    """Calculate Lost Productivity due to downtime"""
    df['downtime_hours'] = pd.to_numeric(df['downtime_minutes'], errors='coerce').fillna(0) / 60.0
    df['standard_output_rate'] = pd.to_numeric(df['standard_output_rate'], errors='coerce').fillna(0)
    df['lost_output'] = df['downtime_hours'] * df['standard_output_rate']
    return df

def extract_relevant_data(df: pd.DataFrame, question: str, max_rows: int = 50) -> pd.DataFrame:
    """Extract relevant rows - INCREASED from 30 to 50 for better coverage"""
    try:
        question_lower = question.lower()
        
        # Try to find specific matches first
        for col in df.columns:
            if df[col].dtype == 'object':
                for val in df[col].dropna().unique():
                    val_str = str(val).lower()
                    if val_str and val_str in question_lower:
                        filtered = df[df[col] == val]
                        if len(filtered) > 0:
                            print(f"🎯 Found match: {col}='{val}' ({len(filtered)} rows)")
                            return filtered.head(max_rows)
        
        print(f"📊 No specific match, showing all {min(len(df), max_rows)} rows")
        return df.head(max_rows)
    except Exception as e:
        print(f"⚠️ Error in extract_relevant_data: {str(e)}")
        return df.head(max_rows)

# ============================================================================
# CONTEXT BUILDING - FIXED VERSION
# ============================================================================

def build_smart_context(data: Dict[str, Any], data_source: str, question: str) -> str:
    """Build smart context - FIXED to handle small datasets"""
    
    try:
        print(f"🔍 Building context for: {data_source}")
        
        if not data or 'data' not in data or len(data['data']) == 0:
            return "ERROR: No data available"
        
        df = pd.DataFrame(data['data'])
        print(f"✅ DataFrame: {len(df)} rows, {len(df.columns)} columns")
        
        question_lower = question.lower()
        
        # ============================================================
        # PRODUCTION QUESTIONS - SIMPLIFIED & ALWAYS WORKS!
        # ============================================================
        if data_source == 'production':
            print("🎯 Detected PRODUCTION question")
            
            context = f"=== PRODUCTION RECORDS ===\n"
            context += f"Total records: {len(df)}\n"
            
            if len(df) == 0:
                return "ERROR: No production data available in database"
            
            # Type cast numeric fields
            numeric_cols = ['units_produced', 'good_units', 'defective_units', 
                           'downtime_minutes', 'standard_output_rate', 'productive_hours']
            for col in numeric_cols:
                if col in df.columns:
                    df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)
            
            # SHOW ALL DATA - don't filter anything for small datasets
            context += f"\n=== PRODUCTION DATA ===\n"
            
            for idx, row in df.iterrows():
                worker_name = row.get('worker_name', 'Unknown')
                date = row.get('date', 'N/A')
                shift = row.get('shift_name', 'N/A')
                units = int(row.get('units_produced', 0))
                good = int(row.get('good_units', 0))
                defects = int(row.get('defective_units', 0))
                dept = row.get('department', 'N/A')
                
                context += f"\n{idx+1}. {worker_name} ({dept})\n"
                context += f"   Date: {date}\n"
                context += f"   Shift: {shift}\n"
                context += f"   Units Produced: {units}\n"
                context += f"   Good Units: {good}\n"
                context += f"   Defective: {defects}\n"
            
            # Add summary stats
            total_units = df['units_produced'].sum()
            total_good = df['good_units'].sum() if 'good_units' in df.columns else 0
            total_defects = df['defective_units'].sum() if 'defective_units' in df.columns else 0
            
            context += f"\n\n=== SUMMARY ===\n"
            context += f"Total Units Produced: {int(total_units)}\n"
            context += f"Total Good Units: {int(total_good)}\n"
            context += f"Total Defective: {int(total_defects)}\n"
            
            if 'shift_name' in df.columns and df['shift_name'].notna().sum() > 0:
                shift_summary = df.groupby('shift_name')['units_produced'].agg(['sum', 'count'])
                context += f"\n=== BY SHIFT ===\n"
                for shift, row in shift_summary.iterrows():
                    context += f"{shift}: {int(row['sum'])} units ({int(row['count'])} records)\n"
            
            if 'worker_name' in df.columns:
                worker_summary = df.groupby('worker_name')['units_produced'].sum().sort_values(ascending=False)
                context += f"\n=== TOP PRODUCERS ===\n"
                for idx, (worker, units) in enumerate(worker_summary.head(10).items(), 1):
                    context += f"{idx}. {worker}: {int(units)} units\n"
            
            return context
        
        # ============================================================
        # OLE QUESTIONS - BETTER ERROR HANDLING
        # ============================================================
        if data_source == 'ole':
            print("🎯 Detected OLE question")
            
            context = f"=== OVERALL LABOR EFFECTIVENESS (OLE) ANALYSIS ===\n"
            context += f"Total records: {len(df)}\n\n"
            
            if len(df) == 0:
                return "ERROR: No production data available for OLE calculation"
            
            # Try to calculate OLE
            df = calculate_ole_metrics(df)
            df = calculate_lost_productivity(df)
            
            # Check if we have valid OLE data
            has_valid_ole = 'ole' in df.columns and df['ole'].sum() > 0
            
            if not has_valid_ole:
                # Show what data we DO have
                context += "⚠️ Cannot calculate OLE - missing required data:\n"
                context += "   - Need: actual_work_time (from attendance)\n"
                context += "   - Need: standard_output_rate\n"
                context += "   - Need: scheduled_hours\n\n"
                
                context += "=== AVAILABLE PRODUCTION DATA ===\n"
                for idx, row in df.head(20).iterrows():
                    worker = row.get('worker_name', 'Unknown')
                    units = int(row.get('units_produced', 0))
                    date = row.get('date', 'N/A')
                    context += f"{idx+1}. {worker}: {units} units on {date}\n"
                
                context += f"\n💡 To enable OLE calculation:\n"
                context += f"   1. Ensure attendance records exist (for actual_work_time)\n"
                context += f"   2. Set standard_output_rate in production_records\n"
                return context
            
            # Has valid OLE - show it
            df_valid = df[df['ole'] > 0]
            avg_ole = df_valid['ole'].mean()
            
            context += f"=== OVERALL OLE METRICS ===\n"
            context += f"Average OLE: {avg_ole:.1f}%\n\n"
            
            context += f"=== WORKER OLE RANKINGS ===\n"
            for idx, row in df_valid.head(15).iterrows():
                worker = row.get('worker_name', 'Unknown')
                ole = row.get('ole', 0)
                avail = row.get('availability', 0) * 100
                perf = row.get('performance', 0) * 100
                qual = row.get('quality', 0) * 100
                
                context += f"\n{idx+1}. {worker} - OLE: {ole:.1f}%\n"
                context += f"   Availability: {avail:.1f}%\n"
                context += f"   Performance: {perf:.1f}%\n"
                context += f"   Quality: {qual:.1f}%\n"
            
            return context
        
        # ============================================================
        # QUALITY QUESTIONS
        # ============================================================
        if data_source == 'quality':
            print("🎯 Detected QUALITY question")
            
            context = f"=== QUALITY INSPECTION RECORDS ===\n"
            context += f"Total records: {len(df)}\n"
            
            if len(df) == 0:
                return "ERROR: No quality data available"
            
            # Type cast
            df['defective_units'] = pd.to_numeric(df['defective_units'], errors='coerce').fillna(0)
            df['units_produced'] = pd.to_numeric(df['units_produced'], errors='coerce').fillna(0)
            df['good_units'] = pd.to_numeric(df['good_units'], errors='coerce').fillna(0)
            
            total_defects = df['defective_units'].sum()
            total_units = df['units_produced'].sum()
            total_good = df['good_units'].sum()
            
            context += f"\n=== OVERALL QUALITY METRICS ===\n"
            context += f"Total Defects: {int(total_defects)}\n"
            context += f"Total Good Units: {int(total_good)}\n"
            context += f"Total Units: {int(total_units)}\n"
            
            if total_units > 0:
                defect_rate = (total_defects / total_units) * 100
                context += f"Defect Rate: {defect_rate:.2f}%\n"
                context += f"First Pass Yield: {100 - defect_rate:.2f}%\n\n"
            
            # Show all quality data
            context += "=== QUALITY BY WORKER ===\n"
            for idx, row in df.head(20).iterrows():
                worker = row.get('worker_name', 'Unknown')
                units = int(row.get('units_produced', 0))
                defects = int(row.get('defective_units', 0))
                date = row.get('date', 'N/A')
                
                defect_pct = (defects / units * 100) if units > 0 else 0
                
                context += f"\n{idx+1}. {worker} ({date})\n"
                context += f"   Units: {units} | Defects: {defects} ({defect_pct:.1f}%)\n"
            
            return context
        
        # ============================================================
        # DOWNTIME QUESTIONS
        # ============================================================
        if data_source == 'downtime':
            print("🎯 Detected DOWNTIME question")
            
            context = f"=== DOWNTIME RECORDS ===\n"
            context += f"Total records: {len(df)}\n"
            
            if len(df) == 0:
                return "ERROR: No downtime data available"
            
            df['downtime_minutes'] = pd.to_numeric(df['downtime_minutes'], errors='coerce').fillna(0)
            df = calculate_lost_productivity(df)
            
            total_downtime = df['downtime_minutes'].sum() / 60
            total_lost = df['lost_output'].sum()
            
            context += f"\n=== DOWNTIME SUMMARY ===\n"
            context += f"Total Downtime: {total_downtime:.2f} hours\n"
            context += f"Total Lost Output: {int(total_lost)} units\n\n"
            
            # Show all downtime data
            context += "=== DOWNTIME BY WORKER ===\n"
            for idx, row in df.head(20).iterrows():
                worker = row.get('worker_name', 'Unknown')
                downtime = row.get('downtime_minutes', 0) / 60
                lost = int(row.get('lost_output', 0))
                date = row.get('date', 'N/A')
                
                context += f"\n{idx+1}. {worker} ({date})\n"
                context += f"   Downtime: {downtime:.2f} hours | Lost: {lost} units\n"
            
            return context
        
        # ============================================================
        # PRODUCTIVITY QUESTIONS
        # ============================================================
        if data_source == 'productivity':
            print("🎯 Detected PRODUCTIVITY question")
            
            context = f"=== PRODUCTIVITY ANALYSIS ===\n"
            context += f"Total records: {len(df)}\n\n"
            
            if len(df) == 0:
                return "ERROR: No productivity data available"
            
            # Type cast
            df['units_produced'] = pd.to_numeric(df['units_produced'], errors='coerce').fillna(0)
            df['hours_worked'] = pd.to_numeric(df['hours_worked'], errors='coerce').fillna(0)
            df['productive_hours'] = pd.to_numeric(df['productive_hours'], errors='coerce').fillna(0)
            
            # Use whichever hours column has data
            if df['hours_worked'].sum() > 0:
                hours_col = 'hours_worked'
            elif df['productive_hours'].sum() > 0:
                hours_col = 'productive_hours'
            else:
                context += "⚠️ No hours data available (hours_worked or productive_hours)\n\n"
                context += "=== PRODUCTION DATA (without hours) ===\n"
                for idx, row in df.head(20).iterrows():
                    worker = row.get('worker_name', 'Unknown')
                    units = int(row.get('units_produced', 0))
                    date = row.get('date', 'N/A')
                    context += f"{idx+1}. {worker}: {units} units on {date}\n"
                return context
            
            # Calculate units per hour
            df_valid = df[df[hours_col] > 0].copy()
            df_valid['units_per_hour'] = df_valid['units_produced'] / df_valid[hours_col]
            
            avg_uph = df_valid['units_per_hour'].mean()
            
            context += f"=== PRODUCTIVITY METRICS ===\n"
            context += f"Average Units per Hour: {avg_uph:.2f}\n\n"
            
            context += "=== WORKER PRODUCTIVITY ===\n"
            for idx, row in df_valid.head(20).iterrows():
                worker = row.get('worker_name', 'Unknown')
                units = int(row.get('units_produced', 0))
                hours = row.get(hours_col, 0)
                uph = row.get('units_per_hour', 0)
                date = row.get('date', 'N/A')
                
                context += f"\n{idx+1}. {worker} ({date})\n"
                context += f"   Units: {units} | Hours: {hours:.1f} | UPH: {uph:.2f}\n"
            
            return context
        
        # ============================================================
        # DEFAULT: SHOW ALL DATA
        # ============================================================
        context = f"=== {data_source.upper()} DATABASE ===\n"
        context += f"Total records: {len(df)}\n\n"
        
        # Show first 50 rows with important columns
        important_cols = [col for col in df.columns 
                         if any(kw in col.lower() for kw in 
                               ['name', 'id', 'date', 'units', 'department', 'shift'])]
        
        if not important_cols:
            important_cols = df.columns.tolist()[:8]
        
        for idx, row in df.head(50).iterrows():
            context += f"Record {idx+1}:\n"
            for col in important_cols:
                if pd.notna(row[col]):
                    context += f"  {col}: {str(row[col])[:60]}\n"
            context += "\n"
        
        return context
        
    except Exception as e:
        print(f"❌ Error in build_smart_context: {str(e)}")
        import traceback
        traceback.print_exc()
        return f"ERROR: {str(e)}"

# ============================================================================
# AI FUNCTIONS
# ============================================================================

def ask_ai(question: str, data_context: str, data_source: str) -> str:
    """Ask Groq AI to analyze the data"""
    
    if data_context.startswith("ERROR"):
        return f"⚠️ {data_context.replace('ERROR: ', '')}"
    
    if len(data_context) < 50:
        return f"⚠️ Not enough data to analyze."
    
    try:
        print(f"\n⚡ Asking Groq AI...")
        print(f"📄 Context size: {len(data_context)} chars")
        
        headers = {
            "Authorization": f"Bearer {GROQ_API_KEY}",
            "Content-Type": "application/json"
        }
        
        payload = {
            "model": GROQ_MODEL,
            "messages": [{
                "role": "system",
                "content": "You are a data analyst. Answer questions accurately using ONLY the data provided. Be concise and clear."
            }, {
                "role": "user",
                "content": f"""DATA:
{data_context}

QUESTION: {question}

Provide a clear answer using ONLY the data above. If data is incomplete, explain what's missing."""
            }],
            "max_tokens": 500,
            "temperature": 0.1
        }
        
        response = requests.post(
            GROQ_URL,
            headers=headers,
            json=payload,
            timeout=15
        )
        
        if response.status_code == 200:
            result = response.json()
            ai_response = result["choices"][0]["message"]["content"]
            print(f"✅ Groq responded! ({len(ai_response)} chars)")
            return ai_response
        else:
            error_msg = response.text
            print(f"❌ Groq API error: {response.status_code}")
            return f"API Error ({response.status_code}): {error_msg[:200]}"
            
    except requests.exceptions.Timeout:
        print("⏱️ Request timeout")
        return "⏱️ Request timeout. Please try again."
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return f"❌ Error: {str(e)}"

# ============================================================================
# ENDPOINTS
# ============================================================================

@app.post("/api/admin/chat", response_model=ChatResponse)
async def admin_chat(message: ChatMessage):
    """Main chat endpoint"""
    try:
        question = message.message
        data_source = message.data_source
        data = message.data
        
        print(f"\n{'='*70}")
        print(f"📨 Question: {question}")
        print(f"📂 Data source: {data_source}")
        
        if data:
            print(f"📊 Total records: {len(data.get('data', []))}")
        
        print(f"{'='*70}")
        
        if not data or 'data' not in data or len(data.get('data', [])) == 0:
            return ChatResponse(
                response=f"❌ No data available in '{data_source}' table. Please add production records to the database.",
                timestamp=datetime.now().isoformat(),
                data_source=data_source
            )
        
        context = build_smart_context(data, data_source, question)
        
        if context.startswith("ERROR"):
            return ChatResponse(
                response=f"⚠️ {context.replace('ERROR: ', '')}",
                timestamp=datetime.now().isoformat(),
                data_source=data_source
            )
        
        ai_response = ask_ai(question, context, data_source)
        
        return ChatResponse(
            response=ai_response,
            timestamp=datetime.now().isoformat(),
            data_source=data_source
        )
        
    except Exception as e:
        print(f"❌ Fatal error: {str(e)}")
        import traceback
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/admin/status")
async def system_status():
    """Check system status"""
    groq_ready = test_groq()
    
    return {
        "status": "ready" if groq_ready else "degraded",
        "ai_service": "Groq",
        "groq_running": groq_ready,
        "model": GROQ_MODEL,
        "version": "10.0.0-FIXED-SMALL-DATASETS",
        "improvements": [
            "✅ Works with ANY amount of data (even 1 record)",
            "✅ Better error messages when data is incomplete",
            "✅ Shows what data IS available",
            "✅ Explains what's missing for advanced metrics"
        ],
        "timestamp": datetime.now().isoformat()
    }

@app.get("/")
async def root():
    """API info"""
    return {
        "name": "Workwise Analytics API",
        "version": "10.0 - FIXED for Small Datasets",
        "status": "running",
        "ai_service": "Groq",
        "model": GROQ_MODEL,
        "features": [
            "✅ Production queries work with ANY data",
            "✅ Better handling of incomplete datasets",
            "✅ Clear error messages",
            "✅ Shows available data even when metrics can't be calculated"
        ],
        "endpoints": {
            "chat": "/api/admin/chat",
            "status": "/api/admin/status",
            "docs": "/docs"
        }
    }

if __name__ == "__main__":
    import uvicorn
    print("\n" + "="*60)
    print("🚀 Workwise Analytics API v10.0 - FIXED VERSION")
    print("✨ Now works with small/incomplete datasets")
    print("="*60)
    print("📍 Server: http://localhost:8001")
    print("📚 Docs: http://localhost:8001/docs")
    print("="*60 + "\n")
    
    uvicorn.run(
        app, 
        host="0.0.0.0", 
        port=8001,
        log_level="info"
    )