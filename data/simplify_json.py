import json
import re
import os
import sys
import getopt

"""
TODO:
- Deal with "strings or lists or dictionaries" issue. Standardize data structure across prerequisites and corequisites?
- Update documentation (outdated)
"""


debug_mode = False


def console_print(txt):
	if debug_mode:
		print(txt)


def main():
	module_file, semester_file, min_mode = load_args()

	# Translates original key value in json file to shorter key
	if min_mode:
		minify = {
			"ModuleTitle": "MT"
			, "ModuleCredit": "MC"
			, "Semester": "SM"
			#, "Preclusion": "PC"
			#, "Prerequisite": "PR"
			#, "Corequisite": "CR"
			#, "ParsedPreclusion": "PPC"
			#, "ParsedPrerequisite": "PPR"
			#, "ParsedCorequisite": "PCR"
		}
	else :
		minify = {
			"ModuleTitle": "ModuleTitle"
			, "ModuleCredit": "ModuleCredit"
			, "Semester": "Semester"
			, "Preclusion": "Preclusion"
			, "Prerequisite": "Prerequisites"
			, "Corequisite": "Corequisites"
			, "ParsedPreclusion": "ParsedPreclusion"
			, "ParsedPrerequisite": "ParsedPrerequisites"
			, "ParsedCorequisite": "ParsedCorequisites"
		}

	mod_dict = simplify(module_file, minify)
	# mod_dict = parse_prerequisites(mod_dic, minify)
	# mod_dict = parse_corequisites(mod_dict, minify)
	mod_dict = include_semester_info(mod_dict, semester_file, minify)
	create_json_file(mod_dict, min_mode)

	if debug_mode:
		print len(mod_dict)


def load_args():
	""" Loads arguments into variables

	:return: module_file and semester_file variables
	"""
	module_file = "nusmods_api/moduleInformation.json"
	semester_file = "nusmods_api/moduleList.json"

	try:
		if os.stat(module_file).st_size > 0 and os.stat(semester_file).st_size > 0:
			console_print("Files found and are not empty")
		elif os.stat(module_file).st_size == 0:
			console_print("'" + module_file + "'is empty or missing")
		else:
			console_print("'" + semester_file + "'is empty or missing")
	except OSError:
		sys.exit("No files found")

	try:
		arg = sys.argv[1:]
	except getopt.GetoptError, err:
		sys.exit("Error in getting arguments")

	return module_file, semester_file, arg == ["-min"]


def simplify(module_file, minify):
	""" Removes redundant fields (such as CORS bidding stats and timetables) from the modules.json file, and also shortens
	the names of keys to reduce the file size. Returns a dictionary, that uses module codes as keys, of dictionaries
	containing other information about that module, which is elaborated below.

	Each module dictionary has the following information:
	- ModuleTitle (NM) = Name of this module.
	- ModuleCredit (MC) = No. of credits this module is worth.
	- ParsedPreclusion (PPC) = List of modules that cannot be taken together with this module.
	- Prerequisite (PR) = Plaintext information detailing prerequisites for this module.
	- ParsedPrerequisite (PPR) = String or list or dictionary of modules that are prerequisites for this module.
	- Corequisite (CR) = Plaintext information detailing corequisites for this module.

	:param module_file: File containing module details, kindly provided by NUSMods' API.
	:param minify: Dictionary that translates long parameter names into shorter ones.
	:return: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	"""
	with open(module_file) as json_file:
		modules = json.load(json_file)
		mod_dict = {}

		for module in modules:
			module_code = module["ModuleCode"]
			simplified_info = {}

			# Shorten name of keys
			for key in module:
				if key in minify:
					simplified_info[minify[key]] = module[key]

			# Cast MC into int type to reduce file size
			simplified_info[minify["ModuleCredit"]] = int(simplified_info[minify["ModuleCredit"]])

			mod_dict[module_code] = simplified_info

		# Handle exceptions
		#mod_dict["CS2020"].pop(minify["Corequisite"], None) # Incorrectly listed corequisite

		return mod_dict


def parse_prerequisites(mod_dict, minify):
	return None


def parse_corequisites(mod_dict, minify):
	"""Parses plaintext information detailing corequisites for each module, into lists or dictionaries containing
	corequisite modules.

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:param minify: Dictionary that translates long parameter names into shorter ones.
	:return: mod_dict updated with corequisite modules parsed into lists or dictionaries
	"""
	mod_string = "[A-Z]{2,3}\d{4}[A-Z]*"
	verify_re = re.compile(mod_string)

	for module_code, module_info in mod_dict.iteritems():
		if minify["Corequisite"] in module_info:
			corequisites = verify_re.findall(module_info[minify["Corequisite"]])
			if corequisites:
				module_info[minify["ParsedCorequisite"]] = corequisites if len(corequisites) <= 1 else {" or ": corequisites}

	# Handle exceptions
	mod_dict["IE4220E"][minify["ParsedCorequisite"]] = {" and ": mod_dict["IE4220E"][minify["ParsedCorequisite"]][" or "]}
	mod_dict["IE4230E"][minify["ParsedCorequisite"]] = {" and ": mod_dict["IE4230E"][minify["ParsedCorequisite"]][" or "]}
	mod_dict["YID3201"][minify["ParsedCorequisite"]] = ["YID1201"]
	mod_dict["BMA5011"][minify["ParsedCorequisite"]] = ["BMA5001"]
	mod_dict["FE5218"][minify["ParsedCorequisite"]] = ["FE5102"]
	mod_dict["NUR1119"][minify["ParsedCorequisite"]] = {" or ": ["NUR1120", {" and ": ["NUR2106", "NUR2117"]}]}
	mod_dict["FIN3120E"].pop(minify["ParsedCorequisite"], None) # Not required
	mod_dict["FIN3120D"].pop(minify["ParsedCorequisite"], None) # Not required
	mod_dict["EN3222"][minify["ParsedCorequisite"]] = mod_dict["EN3223"][minify["ParsedCorequisite"]]\
		= mod_dict["EN3224"][minify["ParsedCorequisite"]] = mod_dict["EN3225"][minify["ParsedCorequisite"]]\
		= mod_dict["EN3229"][minify["ParsedCorequisite"]] = mod_dict["EN3241"][minify["ParsedCorequisite"]]\
		= mod_dict["EN3245"][minify["ParsedCorequisite"]] = mod_dict["EN3249"][minify["ParsedCorequisite"]]\
		= mod_dict["EN3263"][minify["ParsedCorequisite"]] = mod_dict["EN3264"][minify["ParsedCorequisite"]]\
		= mod_dict["EN3265"][minify["ParsedCorequisite"]] = mod_dict["EN3271"][minify["ParsedCorequisite"]]\
		= {" or ": ["EN2201", "EN2202", "EN2203", "EN2204"]}

	return mod_dict


def include_semester_info(mod_dict, semester_file, minify):
	"""Extracts info about which semesters modules are offered in, and includes this info in the dictionary of modules

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:param semester_file: File containing info about which semesters modules are offered in, kindly provided by NUSMods' API.
	:param minify: Dictionary that translates long parameter names into shorter ones.
	:return: mod_dict updated with info about which semesters modules are offered in
	"""
	with open(semester_file) as json_file:
		modules = json.load(json_file)

		for module in modules:
			mod_dict[str(module["ModuleCode"])][minify["Semester"]] = module["Semesters"]

		return mod_dict


def create_json_file(mod_dict, min_mode):
	"""Writes the dictionary (that uses module codes as keys) of dictionaries (containing certain module information),
	to a JSON file on disk.

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:param min_mode: Boolean value, tells function if mod_dict was written in minimized mode
	"""
	if min_mode:
		filename = "modules_min.json"
	else:
		filename = "modules.json";

	module_file = file(filename, 'w')
	json.dump(mod_dict, module_file)
	module_file.close()


if __name__ == "__main__":
	main()
