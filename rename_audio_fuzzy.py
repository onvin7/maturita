#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Skript pro přejmenování audio souborů podle ID z SQL tabulky s fuzzy matching
Používá víceúrovňový matching algoritmus pro nalezení odpovídajících názvů
"""

import os
import re
import sys
import argparse
from pathlib import Path
from typing import Dict, List, Tuple, Optional
import unicodedata
import difflib
from datetime import datetime

# Nastavit UTF-8 encoding pro Windows
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')


def normalize_text(text: str) -> str:
    """
    Normalizuje text pro porovnání - odstraní diakritiku, převede na lowercase,
    odstraní speciální znaky a převede na prostý text
    """
    if not text:
        return ""
    
    # Odstranit diakritiku
    text = unicodedata.normalize('NFD', text)
    text = text.encode('ascii', 'ignore').decode('ascii')
    
    # Převedení na lowercase
    text = text.lower()
    
    # Odstranit speciální znaky, ponechat jen alfanumerické a mezery
    text = re.sub(r'[^a-z0-9\s]', '', text)
    
    # Nahradit více mezer jednou
    text = re.sub(r'\s+', ' ', text)
    
    # Odstranit mezery na začátku a konci
    text = text.strip()
    
    return text


def parse_sql_file(sql_file_path: str) -> Tuple[Dict[str, Tuple[int, str]], Dict[int, str]]:
    """
    Parsuje SQL soubor a extrahuje mapování nazev -> id z INSERT příkazů
    Vrátí:
    - slovník: {normalized_nazev: (id, original_nazev)}
    - slovník: {id: original_nazev} pro zobrazení
    """
    print(f"📖 Načítám SQL soubor: {sql_file_path}")
    
    if not os.path.exists(sql_file_path):
        print(f"❌ SQL soubor neexistuje: {sql_file_path}")
        return {}, {}
    
    nazev_to_id = {}
    id_to_original = {}
    
    try:
        with open(sql_file_path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
        
        # Najít všechny INSERT INTO `clanky` sekce
        insert_sections = re.findall(
            r'INSERT INTO `clanky`[^;]+',
            content,
            re.IGNORECASE | re.DOTALL
        )
        
        total_records = 0
        
        for section in insert_sections:
            lines = section.split('\n')
            
            for line in lines:
                try:
                    line = line.strip()
                    if not line or not line.startswith('('):
                        continue
                    
                    line = line.rstrip(',').rstrip(')')
                    
                    # Najít číslo na začátku (ID)
                    id_match = re.match(r'\((\d+)', line)
                    if not id_match:
                        continue
                    
                    article_id = int(id_match.group(1))
                    
                    # Najít druhou hodnotu - nazev (v uvozovkách)
                    after_id = line[id_match.end():].lstrip()
                    if not after_id.startswith(','):
                        continue
                    
                    after_comma = after_id[1:].lstrip()
                    if not after_comma.startswith("'"):
                        continue
                    
                    # Parsovat SQL string
                    nazev = ""
                    i = 1
                    
                    while i < len(after_comma):
                        char = after_comma[i]
                        
                        if char == "'":
                            if i + 1 < len(after_comma) and after_comma[i + 1] == "'":
                                nazev += "'"
                                i += 2
                            else:
                                break
                        elif char == '\\' and i + 1 < len(after_comma):
                            next_char = after_comma[i + 1]
                            if next_char == "'":
                                nazev += "'"
                            elif next_char == "\\":
                                nazev += "\\"
                            else:
                                nazev += char + next_char
                            i += 2
                        else:
                            nazev += char
                            i += 1
                    
                    if nazev:
                        id_to_original[article_id] = nazev
                        normalized_nazev = normalize_text(nazev)
                        
                        if normalized_nazev:
                            if normalized_nazev not in nazev_to_id:
                                nazev_to_id[normalized_nazev] = (article_id, nazev)
                                total_records += 1
                except Exception:
                    continue
        
        print(f"✓ Načteno {total_records} záznamů z SQL souboru")
        
        # Zobrazit prvních 5 příkladů
        print("\n📋 Příklady načtených záznamů:")
        for i, (norm_nazev, (article_id, original)) in enumerate(list(nazev_to_id.items())[:5], 1):
            print(f"   {i}. ID {article_id}: {original[:50]}...")
        
        return nazev_to_id, id_to_original
        
    except Exception as e:
        print(f"❌ Chyba při parsování SQL souboru: {e}")
        import traceback
        traceback.print_exc()
        return {}, {}


def fuzzy_match(filename: str, nazev_map: Dict[str, Tuple[int, str]], top_n: int = 5) -> List[Tuple[int, str, float, str]]:
    """
    Najde nejpodobnější názvy pomocí fuzzy matching
    Vrátí seznam (id, original_nazev, score, method) seřazený podle skóre
    """
    base_name = Path(filename).stem
    normalized_filename = normalize_text(base_name)
    
    if not normalized_filename:
        return []
    
    matches = []
    
    for norm_nazev, (article_id, original_nazev) in nazev_map.items():
        # Fuzzy matching ratio
        ratio = difflib.SequenceMatcher(None, normalized_filename, norm_nazev).ratio()
        matches.append((article_id, original_nazev, ratio, "fuzzy"))
    
    # Seřadit podle skóre (nejlepší první)
    matches.sort(key=lambda x: x[2], reverse=True)
    
    return matches[:top_n]


def keyword_match(filename: str, nazev_map: Dict[str, Tuple[int, str]]) -> List[Tuple[int, str, float, str]]:
    """
    Najde shody podle klíčových slov
    """
    base_name = Path(filename).stem
    normalized_filename = normalize_text(base_name)
    
    if not normalized_filename:
        return []
    
    # Extrahovat prvních 3-5 slov
    filename_words = set(normalized_filename.split()[:5])
    
    if len(filename_words) < 2:
        return []
    
    matches = []
    
    for norm_nazev, (article_id, original_nazev) in nazev_map.items():
        nazev_words = set(norm_nazev.split())
        common_words = filename_words & nazev_words
        
        if len(common_words) >= 2:
            # Spočítat procentuální shodu
            min_words = min(len(filename_words), len(nazev_words))
            score = len(common_words) / min_words if min_words > 0 else 0
            matches.append((article_id, original_nazev, score, "keywords"))
    
    matches.sort(key=lambda x: x[2], reverse=True)
    return matches[:5]


def partial_match(filename: str, nazev_map: Dict[str, Tuple[int, str]]) -> List[Tuple[int, str, float, str]]:
    """
    Najde shody podle částečné shody začátku
    """
    base_name = Path(filename).stem
    normalized_filename = normalize_text(base_name)
    
    if not normalized_filename or len(normalized_filename) < 10:
        return []
    
    # Prvních 20-30 znaků
    filename_start = normalized_filename[:30]
    
    matches = []
    
    for norm_nazev, (article_id, original_nazev) in nazev_map.items():
        if filename_start in norm_nazev or norm_nazev[:30] in normalized_filename:
            # Spočítat podobnost
            ratio = difflib.SequenceMatcher(None, filename_start, norm_nazev[:30]).ratio()
            matches.append((article_id, original_nazev, ratio, "partial"))
    
    matches.sort(key=lambda x: x[2], reverse=True)
    return matches[:3]


def find_best_match(filename: str, nazev_map: Dict[str, Tuple[int, str]], 
                   id_to_original: Dict[int, str]) -> Optional[Tuple[int, str, float, str]]:
    """
    Najde nejlepší shodu pomocí víceúrovňového matching algoritmu
    """
    base_name = Path(filename).stem
    normalized_filename = normalize_text(base_name)
    
    if not normalized_filename:
        return None
    
    # Krok 1: Přesná shoda
    if normalized_filename in nazev_map:
        article_id, original_nazev = nazev_map[normalized_filename]
        return (article_id, original_nazev, 1.0, "exact")
    
    # Krok 2: Fuzzy matching
    fuzzy_matches = fuzzy_match(filename, nazev_map, top_n=5)
    if fuzzy_matches and fuzzy_matches[0][2] >= 0.65:
        return fuzzy_matches[0]
    
    # Krok 3: Keyword matching
    keyword_matches = keyword_match(filename, nazev_map)
    if keyword_matches and keyword_matches[0][2] >= 0.5:
        return keyword_matches[0]
    
    # Krok 4: Partial match
    partial_matches = partial_match(filename, nazev_map)
    if partial_matches and partial_matches[0][2] >= 0.6:
        return partial_matches[0]
    
    # Pokud fuzzy matching našel něco s alespoň 0.5, použít to
    if fuzzy_matches and fuzzy_matches[0][2] >= 0.5:
        return fuzzy_matches[0]
    
    return None


def rename_audio_file(old_path: str, new_name: str, audio_dir: str, dry_run: bool = False) -> bool:
    """
    Přejmenuje audio soubor na nový název
    """
    try:
        old_file = Path(old_path)
        new_file = Path(audio_dir) / new_name
        
        if not old_file.exists():
            print(f"   ⚠️ Původní soubor neexistuje: {old_path}")
            return False
        
        if dry_run:
            print(f"   [DRY-RUN] Přejmenoval by: {old_file.name} → {new_name}")
            return True
        
        if new_file.exists():
            try:
                if new_file.samefile(old_file):
                    return True
                else:
                    # Konflikt - vytvořit konfliktní název
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    conflict_name = f"{Path(new_name).stem}_conflict_{timestamp}.mp3"
                    new_file = Path(audio_dir) / conflict_name
                    print(f"   ⚠️ Konflikt - použiji název: {conflict_name}")
            except Exception:
                # Pokud samefile nefunguje, zkusit přepsat
                new_file.unlink()
        
        old_file.rename(new_file)
        return True
    except Exception as e:
        print(f"   ❌ Chyba při přejmenování: {e}")
        return False


def main():
    parser = argparse.ArgumentParser(description='Přejmenování audio souborů podle ID z SQL tabulky')
    parser.add_argument('--limit', type=int, default=0, help='Limit počtu souborů k zpracování (0 = všechny)')
    parser.add_argument('--dry-run', action='store_true', help='Dry-run režim - zobrazí co by se stalo, ale nepřejmenuje')
    parser.add_argument('--threshold', type=float, default=0.65, help='Threshold pro fuzzy matching (default: 0.65)')
    
    args = parser.parse_args()
    
    print("=" * 70)
    print("🎵 PŘEJMENOVÁNÍ AUDIO SOUBORŮ S FUZZY MATCHING")
    print("=" * 70)
    if args.dry_run:
        print("⚠️  DRY-RUN REŽIM - soubory nebudou přejmenovány")
    print()
    
    # Cesty
    workspace_root = Path(__file__).parent.absolute()
    sql_file = r"C:\Users\onvin\Downloads\clanky.sql"
    audio_dir = workspace_root / "web" / "uploads" / "audio"
    
    print(f"📁 Workspace: {workspace_root}")
    print(f"📄 SQL soubor: {sql_file}")
    print(f"📁 Audio složka: {audio_dir}")
    if args.limit > 0:
        print(f"⚠️  Limit: Zpracuji pouze prvních {args.limit} souborů")
    print()
    
    # 1. Načíst data z SQL souboru
    nazev_map, id_to_original = parse_sql_file(sql_file)
    
    if not nazev_map:
        print("❌ Nepodařilo se načíst data z SQL souboru. Ukončuji.")
        sys.exit(1)
    
    print()
    
    # 2. Najít audio soubory
    print(f"🔍 Hledám audio soubory v: {audio_dir}")
    
    try:
        audio_dir_path = Path(audio_dir)
        if not audio_dir_path.exists():
            audio_dir_path.mkdir(parents=True, exist_ok=True)
        
        audio_extensions = ['.mp3', '.wav', '.m4a', '.MP3', '.WAV', '.M4A']
        audio_files = []
        
        for ext in audio_extensions:
            audio_files.extend(audio_dir_path.glob(f'*{ext}'))
        
        audio_files = [str(f) for f in audio_files if f.exists()]
        audio_files = sorted(audio_files)
    except Exception as e:
        print(f"❌ Chyba při hledání audio souborů: {e}")
        sys.exit(1)
    
    if not audio_files:
        print("⚠️ Žádné audio soubory nenalezeny ve složce.")
        sys.exit(0)
    
    print(f"✓ Nalezeno {len(audio_files)} audio souborů")
    
    # Omezit na limit
    if args.limit > 0:
        original_count = len(audio_files)
        audio_files = audio_files[:args.limit]
        print(f"⚠️  Pro testování zpracuji pouze prvních {len(audio_files)} z {original_count} souborů")
    
    print()
    print("🚀 Začínám přejmenovávání...")
    print()
    
    # 3. Zpracovat každý soubor
    renamed_count = 0
    skipped_count = 0
    error_count = 0
    already_correct = 0
    skipped_files = []
    
    for i, audio_file in enumerate(audio_files, 1):
        try:
            filename = Path(audio_file).name
            
            if i > 1:
                print("-" * 70)
            
            print(f"\n[{i}/{len(audio_files)}] 📄 Zpracovávám: {filename}")
            
            # Zkontrolovat, zda už má správný název (číslo.mp3)
            if re.match(r'^\d+\.mp3$', filename):
                already_correct += 1
                print(f"   ✓ Soubor už má správný název")
                continue
            
            # Najít nejlepší shodu
            base_name = Path(filename).stem
            normalized_file = normalize_text(base_name)
            print(f"   🔍 Normalizovaný název: '{normalized_file[:60]}...'")
            
            best_match = find_best_match(filename, nazev_map, id_to_original)
            
            if not best_match:
                skipped_count += 1
                skipped_files.append(filename)
                print(f"   ❌ Nepodařilo se najít odpovídající záznam")
                
                # Zobrazit top možnosti
                fuzzy_matches = fuzzy_match(filename, nazev_map, top_n=3)
                if fuzzy_matches:
                    print(f"   📋 Top 3 možnosti (fuzzy matching):")
                    for sql_id, sql_nazev, score, method in fuzzy_matches:
                        print(f"      ID {sql_id}: {sql_nazev[:50]}... (skóre: {score:.1%})")
                
                print(f"   ⏭️  Přeskočeno")
                continue
            
            article_id, original_nazev, score, method = best_match
            
            print(f"   ✓ Nalezeno: ID {article_id} ({method}, skóre: {score:.1%})")
            print(f"   📝 Název v SQL: {original_nazev[:60]}...")
            
            # Nový název: {id}.mp3
            new_filename = f"{article_id}.mp3"
            
            # Přejmenovat
            print(f"   🔄 Přejmenovávám: {filename} → {new_filename}")
            
            if rename_audio_file(audio_file, new_filename, str(audio_dir), args.dry_run):
                renamed_count += 1
                print(f"   ✅ Úspěšně přejmenováno")
            else:
                error_count += 1
                print(f"   ❌ Chyba při přejmenování")
                
        except Exception as e:
            error_count += 1
            print(f"\n   ❌ Neočekávaná chyba: {e}")
            continue
    
    # 4. Statistiky
    print()
    print("=" * 70)
    print("✅ DOKONČENO")
    print("=" * 70)
    print(f"Celkem souborů: {len(audio_files)}")
    print(f"✅ Přejmenováno: {renamed_count}")
    print(f"✓ Už správně pojmenováno: {already_correct}")
    print(f"⏭️  Přeskočeno: {skipped_count} (nenalezen odpovídající záznam)")
    print(f"❌ Chyb: {error_count}")
    
    if skipped_count > 0:
        print()
        print("📋 Přeskočené soubory:")
        for skipped_file in skipped_files[:20]:
            print(f"   - {skipped_file}")
        if len(skipped_files) > 20:
            print(f"   ... a dalších {len(skipped_files) - 20} souborů")


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n⚠️ Přerušeno uživatelem")
        sys.exit(1)
    except Exception as e:
        print(f"\n\n❌ Neočekávaná chyba: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)




