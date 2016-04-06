"""
TODO:
- Deal with "strings or lists or dictionaries" issue. Standardize data structure across prerequisites and corequisites
"""

import json
import sys
import getopt
import re


debug_mode = True


minify = {
	"ModuleCode": "CD",
	"ModuleTitle": "MT",
	"ModuleCredit": "MC",
	"ParsedPreclusion": "PPP",
	"Prerequisite": "RQ",
	"ParsedPrerequisite": "PRQ",
	"Corequisite": "CR",
	"ParsedCorequisite": "PCR"
}


def main():
	module_file, semester_file = load_args()
	mod_dict = simplify(module_file)
	mod_dict = parse_corequisites(mod_dict)
	mod_dict = include_semester_info(mod_dict, semester_file)
	create_json_file(mod_dict)

	if debug_mode:
		print len(mod_dict)
		cr_file = file("corequisites.json", 'w')
		for module_code, module_info in mod_dict.iteritems():
			if "CR" in module_info:
				cr_file.write(module_code)
				cr_file.write("\n")
				cr_file.write(module_info["CR"].encode('ascii', 'ignore'))
				cr_file.write("\n")
				json.dump(module_info["PCR"], cr_file)
				cr_file.write("\n\n")
		cr_file.close()


def load_args():
	""" Loads arguments into variables

	:return: module_file and semester_file variables
	"""
	module_file = semester_file = None

	try:
		opts, args = getopt.getopt(sys.argv[1:], 'm:s:')
	except getopt.GetoptError, err:
		usage()
		sys.exit(2)
	for o, a in opts:
		if o == '-m':
			module_file = a
		elif o == '-s':
			semester_file = a
		else:
			assert False, "unhandled option"
	if module_file is None:
		usage()
		sys.exit(2)
	return module_file, semester_file


def usage():
	"""Prints the proper format for calling this script."""
	print "usage: " + sys.argv[0] + " -m module_file -s semester_file"


def simplify(module_file):
	""" Removes redundant fields (such as CORS bidding stats and timetables) from the modules.json file, and also shortens
	the names of keys to reduce the file size. Returns a dictionary, that uses module codes as keys, of dictionaries
	containing other information about that module, which is elaborated below.

	Each module dictionary has the following information:
	- ModuleTitle (NM) = Name of this module.
	- ModuleCredit (MC) = No. of credits this module is worth.
	- ParsedPreclusion (PC) = List of modules that cannot be taken together with this module.
	- Prerequisite (PR) = Plaintext information detailing prerequisites for this module.
	- ParsedPrerequisite (PP) = String or list or dictionary of modules that are prerequisites for this module.
	- Corequisite (CR) = Plaintext information detailing corequisites for this module.

	:param module_file: File containing module details, kindly provided by NUSMods' API.
	:return: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	"""
	with open(module_file) as json_file:
		modules = json.load(json_file)
		mod_dict = {}

		for module in modules:
			simplified_info = {}

			# Shorten name of keys
			for key in module:
				if key in minify:
					simplified_info[minify[key]] = module[key]

			mod_dict[module["ModuleCode"]] = simplified_info

		# Handle exceptions
		mod_dict["CS2020"].pop("CR", None) # Incorrectly listed corequisite

		return mod_dict


def parse_corequisites(mod_dict):
	"""Parses plaintext information detailing corequisites for each module, into lists or dictionaries containing
	corequisite modules.

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:return: mod_dict updated with corequisite modules parsed into lists or dictionaries
	"""
	mod_string = "[A-Z]{2,3}\d{4}[A-Z]*"
	verify_re = re.compile(mod_string)

	for module_code, module_info in mod_dict.iteritems():
		if "CR" in module_info:
			corequisites = verify_re.findall(module_info["CR"])
			module_info["PCR"] = corequisites if len(corequisites) <= 1 else {" or ": corequisites}

	# Handle exceptions
	mod_dict["IE4220E"]["PCR"] = {" and ": mod_dict["IE4220E"]["PCR"][" or "]}
	mod_dict["IE4230E"]["PCR"] = {" and ": mod_dict["IE4230E"]["PCR"][" or "]}
	mod_dict["YID3201"]["PCR"] = ["YID1201"]
	mod_dict["BMA5011"]["PCR"] = ["BMA5001"]
	mod_dict["FE5218"]["PCR"] = ["FE5102"]
	mod_dict["NUR1119"]["PCR"] = {" or ": ["NUR1120", {" and ": ["NUR2106", "NUR2117"]}]}
	mod_dict["FIN3120E"]["PCR"] = []
	mod_dict["FIN3120D"]["PCR"] = []
	mod_dict["EN3222"]["PCR"] = mod_dict["EN3223"]["PCR"] = mod_dict["EN3224"]["PCR"]\
		= mod_dict["EN3225"]["PCR"] = mod_dict["EN3229"]["PCR"] = mod_dict["EN3241"]["PCR"]\
		= mod_dict["EN3245"]["PCR"] = mod_dict["EN3249"]["PCR"] = mod_dict["EN3263"]["PCR"]\
		= mod_dict["EN3264"]["PCR"] = mod_dict["EN3265"]["PCR"] = mod_dict["EN3271"]["PCR"]\
		= {" or ": ["EN2201", "EN2202", "EN2203", "EN2204"]}

	return mod_dict


def include_semester_info(mod_dict, semester_file):
	"""Extracts info about which semesters modules are offered in, and includes this info in the dictionary of modules

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:param semester_file: File containing info about which semesters modules are offered in, kindly provided by NUSMods' API.
	:return: mod_dict updated with info about which semesters modules are offered in
	"""
	with open(semester_file) as json_file:
		mod_sems = json.load(json_file)

		for module in mod_sems:
			mod_dict[str(module["ModuleCode"])]["SM"] = module["Semesters"]

		return mod_dict


def create_json_file(mod_dict):
	"""Writes the dictionary (that uses module codes as keys) of dictionaries (containing certain module information),
	to a JSON file on disk.

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	"""
	module_file = file("simplified.json", 'w')
	json.dump(mod_dict, module_file)
	module_file.close()


if __name__ == "__main__":
	main()
